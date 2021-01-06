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
class MigrateClientsCommand extends Command
{

    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
    }

    /**
     *
     * Execute the command for import_metrics
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {

        $clients = $this->mystaffClientsTable->find()
            ->where([
                'active' => 'yes'
            ]);

        $countProcessed = 0;
        $countFailed    = 0;
        $failed         = '';
        foreach ($clients as $client) {

            $wms_client = $this->clientsTable->find()
                ->where([
                    'name' => $client->name
                ])
                ->first();

            if($wms_client) {

                $clientData = [
                    'short_code' => strtolower($client->shorthand),
                ];

                $clientEntity = $this->clientsTable->patchEntity($wms_client, $clientData);

                if ($this->clientsTable->save($clientEntity)) {
                    $countProcessed++;
                }else{
                    $countFailed++;

                    $failed .= $client->name . ', ';
                }

            } else {

                $newClientData = [
                    'name' => strtolower($client->name),
                    'short_code' => strtolower($client->shorthand)
                ];

                $newClientEntity = $this->clientsTable->newEntity($newClientData);

                if ($this->clientsTable->save($newClientEntity)) {
                    $countProcessed++;
                }else{
                    $countFailed++;

                    $failed .= $client->name . ', ';
                }

            }

        }

        $wmsClients = $this->clientsTable->find('all')
            ->where([
                'short_code is' => null
            ]);


        if($wmsClients) {
            foreach ($wmsClients as $wmsClient) {

                $client_details = $this->mystaffClientsTable->find()
                    ->where([
                        'LOWER(name)' => strtolower($wmsClient->name)
                    ])
                    ->first();

                if($client_details) {

                    $client_data = [
                        'short_code' => strtolower($client->shorthand),
                    ];

                    $clientEntity = $this->clientsTable->patchEntity($wmsClient, $client_data);

                }
            }
        }

        $io->out("Success updates: " . $countProcessed);
        $io->out("Failed updates: " . $countProcessed);
        $io->out("Failed Clients: " . $failed);

    } // end of execute

}
