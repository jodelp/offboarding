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
 * Fill data for Client's with empty shortcodes. Use client name as reference.
 * Copy client shortcode from MyStaff clients table (shorthand).
 *
 */
class MigrateShortcodeCommand extends Command
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

        $wms_clients_list = $this->clientsTable->find()
            ->where([
                'OR' => [
                    'short_code is' => null,
                    'short_code' => ''
                ]

            ])
            ->toArray();

        $failed_client_id = '';
        $not_found_clients_ids = '';
        $not_found_clients_names = '';
        if($wms_clients_list) {
            foreach($wms_clients_list as $client) {

                $mystaff_clients = $this->mystaffClientsTable->find()
                    ->where([
                        'LOWER(name)' => $client->name
                    ])
                    ->first();

                if($mystaff_clients) {

                    $clientData = [
                        'short_code' => strtolower($mystaff_clients->shorthand),
                    ];

                    $clientEntity = $this->clientsTable->patchEntity($client, $clientData);

                    if ($this->clientsTable->save($clientEntity)) {
                        continue;
                    }else{
                        $failed_client_id .= $client->id . ', ';
                    }

                } else {
                    $not_found_clients_ids .= $client->id . ', ';
                    $not_found_clients_names .= $client->name . ', ';
                }

            }
        }


        $io->out("Not Found Clients IDs: " . $not_found_clients_ids);
        $io->out("Not Found Clients Names: " . $not_found_clients_names);
        $io->out("Failed updates: " . $failed_client_id);

    } // end of process

}
