<?php

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

/**
 * Migrate data from MyStaff workbench_settings to Workbench MS
 */
class WorkbenchSettingsCommand extends Command
{

    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->workbenchSettingsTable = TableRegistry::getTableLocator()->get('WorkbenchSettings');
        $this->mystaffWorkbenchSettingsTable = TableRegistry::getTableLocator()->get('MystaffWorkbenchSettings');
        $this->mystaffStaffsTable = TableRegistry::getTableLocator()->get('MystaffStaffs');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
    }

    /**
     *
     * Execute the command workbench_settings
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {

      $workbenchSettings = $this->mystaffWorkbenchSettingsTable->find()->contain(['MystaffStaffs'])->all();

      foreach ($workbenchSettings as $workbench) {

        $staffEntity = $this->staffsTable->find()->where(['username' => $workbench->mystaff_staff->email])->first();
        if (empty($staffEntity)) {
            /**
            * Create staff if not exist
            */
            $staffEntity = $this->staffsTable->establish($workbench->mystaff_staff->email);
        }
        $mystaffClient = $this->mystaffClientsTable->getShortHand($workbench->client_id);
        if (!isset($mystaffClient)) {
            Log::info('Skipping this record. Empty shorthand', ['scope' => ['migration']]);
            Log::info('Client ID: '. $workbench->client_id . ')', ['scope' => ['migration']]);
            continue;
        } else {
          if (empty($mystaffClient->shorthand)) {
              Log::info('Skipping this record. Empty shorthand', ['scope' => ['migration']]);
              Log::info('Client name: '. $mystaffClient->name . ' (' . $mystaffClient->client_id . ')', ['scope' => ['migration']]);
              continue;
          } else {
              $clientEntity = $this->clientsTable->find()->where(['short_code' => $mystaffClient->shorthand])->first();
              if (empty($clientEntity)) {
                  Log::info('Skipping this record. No client found in our records'. ' - ' .$mystaffClient->shorthand, ['scope' => ['migration']]);
                  Log::info('Client name: '. $mystaffClient->name . ' (' . $mystaffClient->client_id . ')', ['scope' => ['migration']]);
                  continue;
              } else {
                $workbenchSettingEntity = $this->workbenchSettingsTable->newEntity([
                    'staff_id' => $staffEntity->id,
                    'client_id' => $clientEntity->id,
                    'screen_capture' => $workbench->screen_capture / 1000, // convert to second
                    'idle_time_starts_after' => $workbench->idle_time_starts_after / 1000, //convert to second
                    'created' => date('Y-m-d'),
                    'modified' => date('Y-m-d'),
                ]);
                $this->workbenchSettingsTable->save($workbenchSettingEntity);
              }
          }
        }


      }//end of foreach
        $io->out("Done Processing.");
    } // end of execute

}
