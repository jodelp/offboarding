<?php

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Console\ConsoleOptionParser;

/**
 * Test Gearman Connectivity
 */
class GearmanTestCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Hello world.');


        // Create our client object
        $client = new \GearmanClient();

        // Add a server
        $client->addServer(GEARMAN_HOST);

        echo "Sending job\n";

        // Send reverse job
//        $result = $client->doNormal("ping", "Hello!");
        $result = $client->doBackground("ping", 'test worker');
        if ($result) {
            echo "Success: $result\n";
        }


    }

}
