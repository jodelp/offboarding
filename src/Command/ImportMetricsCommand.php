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
class ImportMetricsCommand extends Command
{

    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const STATUS_SUCCESS = 'Success';
    const TEAM_TYPE = 2;
    const STAFF_TYPE = 3;
    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->mystaffMetricsTable = TableRegistry::getTableLocator()->get('MystaffMetrics');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->mystaffTeamsTable = TableRegistry::getTableLocator()->get('MystaffTeams');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->metricsTable = TableRegistry::getTableLocator()->get('Metrics');
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->mystaffMetricAssignTable = TableRegistry::getTableLocator()->get('MystaffMetricAssignees');
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
      $metrics = $this->mystaffMetricsTable->find()->order(['id desc'])->all();

      foreach ($metrics as $metric) {
          $teamVal = true;
          $mystaffClient = $this->mystaffClientsTable->find()->where(['client_id' => $metric->client_id])->first();
          if (empty($mystaffClient)) {
            Log::info('No client found on mystaff clients table'. ' - ' .$metric->client_id , ['scope' => ['migrationTasks']]);
            $teamVal = false;
            continue;
          }
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
             if ($metric->assignee_type == self::STAFF_TYPE) {
               $staffs = [];
               $staffTypes = $this->mystaffMetricAssignTable->find()->contain('MystaffStaffs')->where(['metric_id' => $metric->id])->all();
               foreach ($staffTypes as $staffType) {
                 $staffEntity = $this->staffsTable->establish($staffType->mystaff_staff->email);
                 array_push($staffs, $staffEntity->id);
               }
               $metric->assigned_teams = implode(',',$staffs);
             } elseif ($metric->assignee_type == self::TEAM_TYPE) {
               $teamEntity = $this->mystaffTeamsTable->find()->where(['id' => $metric->assigned_teams])->first();
               if (empty($teamEntity)) {
                 Log::info('No team found on mystaff teams table'. ' - ' .$metric->assigned_teams , ['scope' => ['migrationTasks']]);
                 $teamVal = false;
                 continue;
               } else {
                 $responseTeam = $this->http->get($this->endpoint . $this->endpoint_team, ['name' => $teamEntity->name], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
                 $resultTeam = $responseTeam->getJson();
                 if ($resultTeam['status'] == self::STATUS_SUCCESS) {
                   $metric->assigned_teams = $resultTeam['team_id'];
                 }
               }
             } else {
               $metric->assigned_teams = $metric->assigned_teams;
             }

             $data = [
               'client_id' => $clientEntity->id,
               'assignee_type' => $metric->assignee_type,
               'assign_id' => $metric->assigned_teams,
               'name' => $metric->name,
               'is_deleted' => $metric->is_deleted,
               'created' => $metric->created,
               'modified' => date('Y-m-d H:i:s')
             ];
              $matricsData = $this->metricsTable->newEntity($data);
            if ($this->metricsTable->save($matricsData) && $teamVal) {
              Log::info('Metrics Data successfully save: '. $clientEntity->short_code . ' (' . $clientEntity->id . ')', ['scope' => ['migrationMetrics']]);
            } else {
              Log::info('Unable to save data'. $clientEntity->name , ['scope' => ['migrationMetrics']]);
            }
          } else {
            Log::info('Skipping this record. No client found in CMS records'. ' - ' . (empty($mystaffClient->shorthand) ? 'no record on mystaff' : $mystaffClient->shorthand), ['scope' => ['migrationMetrics']]);
            Log::info('Client name: '. (empty($mystaffClient->name) ? 'no record on mystaff' : $mystaffClient->name) , ['scope' => ['migrationMetrics']]);
            continue;
          }
      }

        $io->out("Done Processing.");
    } // end of execute

}
