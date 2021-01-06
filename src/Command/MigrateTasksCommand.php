<?php

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Cake\Http\Client;
use App\Service\Token;

/**
 * Migrate data from MyStaff metrics to Workbench MS metrics
 * Save the data on metrics table
 * If no client found Log to error logs
 * Else save the data
 *
 */
class MigrateTasksCommand extends Command
{

    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const STATUS_SUCCESS = 'Success';
    const TEAM_TYPE = 2;
    const STAFF_TYPE = 3;
    const CLIENT_GENERAl = 0;
    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->mystaffTasksTable = TableRegistry::getTableLocator()->get('MystaffTasks');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->mystaffTeamsTable = TableRegistry::getTableLocator()->get('MystaffTeams');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->mystaffTaskAssignTable = TableRegistry::getTableLocator()->get('MystaffTaskAssignees');
        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint_name = 'client/details.json';
        $this->endpoint_team = 'team/get.json';
        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD);
    }

    /**
     *
     * Execute the command for import_metrics
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
      $tasks = $this->mystaffTasksTable->find()->contain('MystaffTaskCategories')->order(['MystaffTasks.id desc'])->all();

      foreach ($tasks as $task) {
          $teamVal = true;
          $mystaffClient = $this->mystaffClientsTable->find()->where(['client_id' => $task->client_id])->first();
          $token = new Token();
          $url = $this->endpoint . $this->endpoint_name;
          $response = $this->http->get($url, ['client_code' => strtolower($mystaffClient->shorthand)], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
          $result = $response->getJson();

          if ($result['status'] == self::STATUS_SUCCESS) {
             $clientEntity = $this->clientsTable->find()->where(['short_code' => $result['shortcode']])->first();
             if (empty($clientEntity)) {
               $clientEntity = $this->clientsTable->establish($result['name'], $result['shortcode']);
             }

             //check assignee type [1=> all, 2 => team, 3=>staff]
             if ($task->assignee_type == self::STAFF_TYPE) {
               $staffs = [];
               $staffTypes = $this->mystaffTaskAssignTable->find()->contain('MystaffStaffs')->where(['task_id' => $task->id])->all();
               foreach ($staffTypes as $staffType) {
                 $staffEntity = $this->staffsTable->establish($staffType->mystaff_staff->email);
                 array_push($staffs, $staffEntity->id);
               }
               $task->assigned_teams = implode(',',$staffs);
             } elseif ($task->assignee_type == self::TEAM_TYPE) {
               $teams = explode(',',$task->assigned_teams);
               $teamsData = [];
               foreach ($teams as $team) {
                 $teamEntity = $this->mystaffTeamsTable->find()->where(['id' => $team])->first();
                 if (empty($teamEntity)) {
                   Log::info('No team found on mystaff teams table'. ' - ' .$team , ['scope' => ['migrationTasks']]);
                   $teamVal = false;
                   continue;
                 } else {
                   $responseTeam = $this->http->get($this->endpoint . $this->endpoint_team, ['name' => trim($teamEntity->name)], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
                   $resultTeam = $responseTeam->getJson();
                   if ($resultTeam['status'] == self::STATUS_SUCCESS) {
                     array_push($teamsData, $resultTeam['team_id']);
                   }
                 }
               }
               $task->assigned_teams = implode(',', $teamsData);
             } else {
               $task->assigned_teams = $task->assigned_teams;
             }

             //Check the Task Category ID
             if ($task->mystaff_task_category->client_id == self::CLIENT_GENERAl) {
              $taskCategory = $this->taskCategoriesTable->find()->where(['name' => strtolower($task->mystaff_task_category->name), 'client_id' => self::CLIENT_GENERAl])->first();
            } else {
              $taskCategory = $this->taskCategoriesTable->establish($task->mystaff_task_category->name, $clientEntity);
            }

             $data = [
               'client_id' => $clientEntity->id,
               'assignee_type' => $task->assignee_type,
               'assign_id' => $task->assigned_teams,
               'task_category_id' => $taskCategory->id,
               'name' => $task->name,
               'created' => $task->created,
               'modified' => date('Y-m-d H:i:s')
             ];
              $tasksData = $this->tasksTable->newEntity($data);

            if ($this->tasksTable->save($tasksData) && $teamVal) {
              Log::info('Task Data successfully save: '. $clientEntity->short_code . ' (' . $tasksData->id . ')', ['scope' => ['migrationTasks']]);
            } else {
              Log::info('Unable to save data '. $clientEntity->name , ['scope' => ['migrationTasks']]);
            }
          } else {
            Log::info('Skipping this record. No client found in CMS records'. ' - ' .$mystaffClient->shorthand, ['scope' => ['migrationTasks']]);
            Log::info('Client name: '. $mystaffClient->name . ' (' . $mystaffClient->client_id . ')', ['scope' => ['migrationTasks']]);
            continue;
          }
      }

        $io->out("Done Processing.");
    } // end of execute

}
