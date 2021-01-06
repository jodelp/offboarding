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
class MigrateTaskCategoriesCommand extends Command
{

    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const STATUS_SUCCESS = 'Success';
    const CLIENT_GENERAl = 0;

    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->mystaffTaskCategoriesTable = TableRegistry::getTableLocator()->get('MystaffTaskCategories');
        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint_name = 'client/details.json';
        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD) . $this->endpoint_name;
    }

    /**
     *
     * Execute the command for import_metrics
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
      $taskCategories = $this->mystaffTaskCategoriesTable->find()->all();

      foreach ($taskCategories as $category) {

          if ($category->client_id != self::CLIENT_GENERAl) {
              $mystaffClient = $this->mystaffClientsTable->find()->where(['client_id' => $category->client_id])->first();
              $token = new Token();
              $url = $this->endpoint;
              $response = $this->http->get($url, ['client_code' => strtolower($mystaffClient->shorthand)], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
              $result = $response->getJson();

            if ($result['status'] == self::STATUS_SUCCESS) {

                 $clientEntity = $this->clientsTable->establish($result['name'], $result['shortcode']);

               $taskCategory = $this->taskCategoriesTable->establish($category->name, $clientEntity);

            } else {
              Log::info('Skipping this record. No client found in CMS records'. ' - ' .$mystaffClient->shorthand, ['scope' => ['migrationTaskCategories']]);
              Log::info('Client name: '. $mystaffClient->name , ['scope' => ['migrationTaskCategories']]);
              continue;
            }
          } else {
            //Check if client_id is 0
              $data = [
                'client_id' => self::CLIENT_GENERAl,
                'name' => strtolower($category->name),
              ];

             $taskcategoryEntity = $this->taskCategoriesTable->find()->where(['name' => strtolower($category->name), 'client_id' => self::CLIENT_GENERAl])->first();
             if ($taskcategoryEntity) {
               $tasksData = $this->taskCategoriesTable->patchEntity($taskcategoryEntity,$data);
             } else {
               $tasksData = $this->taskCategoriesTable->newEntity($data);
             }
             if ($this->taskCategoriesTable->save($tasksData)) {
               Log::info('Metrics Data successfully save: '. 0 , ['scope' => ['migrationTaskCategories']]);
             } else {
               Log::info('Unable to save data' , ['scope' => ['migrationTaskCategories']]);
             }
          }
      }

        $io->out("Done Processing.");
    } // end of execute

}
