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
 * Migrate data from MyStaff workbench_settings to Workbench MS
 * Save the data on clients_workbench_setting
 * If no client found Log to error logs
 * Else save the data
 *
 * Convert screen_capture and idle_time_starts_after to seconds bec in mystaff it is presented as millisecond
 */
class DefaultWorkbenchSettingsCommand extends Command
{

    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const STATUS_SUCCESS = 'Success';
    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->clientWorkbenchSettingsTable = TableRegistry::getTableLocator()->get('ClientsWorkbenchSettings');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint_name = 'client/details.json';
        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD) . $this->endpoint_name;
    }

    /**
     *
     * Execute the command for default_workbench_settings
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
      $mystaffClients = $this->mystaffClientsTable->find()->where(['workbench_default_settings !=' => ''])->order('id desc')->all();

      foreach ($mystaffClients as $client) {
        $token = new Token();
        $url = $this->endpoint;
        $response = $this->http->get($url, ['client_code' => $client->shorthand], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
        $result = $response->getJson();

        if ($result['status'] == self::STATUS_SUCCESS) {
           $clientEntity = $this->clientsTable->find()->where(['short_code' => $result['shortcode']])->first();
           if (empty($clientEntity)) {
             $clientEntity = $this->clientsTable->establish($result['name'], $result['shortcode']);
           }
           $default = json_decode($client->workbench_default_settings);
           $clientWorkbenchSetting = $this->clientWorkbenchSettingsTable->find()->where(['client_id' => $clientEntity->id])->first();
           $data = [
             'client_id' => $clientEntity->id,
             'screen_capture' => intval($default->WorkbenchSetting->screen_capture) / 1000,
             'idle_time_starts_after' => intval($default->WorkbenchSetting->idle_time_starts_after) / 1000,
             'created' => date('Y-m-d H:i:s'),
             'modified' => date('Y-m-d H:i:s'),
           ];
           if ($clientWorkbenchSetting) {
             $clientWorkbenchSettingEntity = $this->clientWorkbenchSettingsTable->patchEntity($clientWorkbenchSetting,$data);
           } else {
             $clientWorkbenchSettingEntity = $this->clientWorkbenchSettingsTable->newEntity($data);
           }

           if ($this->clientWorkbenchSettingsTable->save($clientWorkbenchSettingEntity)) {
             Log::info('Default Workbench Settings successfully save: '. $clientEntity->short_code . ' (' . $clientEntity->id . ')', ['scope' => ['migrationDefault']]);
           } else {
             Log::info('Unable to save data'. $clientEntity->name , ['scope' => ['migrationDefault']]);
           }
        } else {
          Log::info('Skipping this record. No client found in CMS records'. ' - ' .$client->shorthand, ['scope' => ['migrationDefault']]);
          Log::info('Client name: '. $client->name . ' (' . $client->client_id . ')', ['scope' => ['migrationDefault']]);
          continue;
        }

      }
        $io->out("Done Processing.");
    } // end of execute

}
