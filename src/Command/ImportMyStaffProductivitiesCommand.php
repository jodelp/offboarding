<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Console\ConsoleOptionParser;

class ImportMyStaffProductivitiesCommand extends Command
{
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser->addArgument('name', [
            'help' => 'Enter File Name'
        ]);
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $fileName = $args->getArgument('name');

        $productivitiesTable = TableRegistry::getTableLocator()->get('Productivities');

        $staffsTable = TableRegistry::getTableLocator()->get('Staffs');

        $clientsTable = TableRegistry::getTableLocator()->get('Clients');

        $taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');

        $file = 'config/Imports/'.$fileName;

        $handle = fopen($file,'r');

        $users = [];
        $ctr = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {

            /**
             * Check if the first row is header
             */
            if (strtolower($row[0]) === 'email') {
                continue;
            } else {
                if ($row[0] != "NULL") {
                    $username = $row[0];
                }else{
                    $username = $row[1];
                }
                /**
                * Create staff if not exist
                */
                $staffEntity = $staffsTable->establish($username);
                $user_id = $staffEntity->id;
                /**
                * Create client if not exist
                */
                $clientEntity = $clientsTable->establish($row[2]);
                $client_id = $clientEntity->id;


                if ($row[3] != "NULL") {
                    if (strtolower($row[3]) === "general") {
                        $task_category_id = 1;
                    }else{
                        /**
                        * Create Task Category if not exist
                        */
                        $taskCategoryEntity = $taskCategoriesTable->establish($row[3], $clientEntity);
                        $task_category_id = $taskCategoryEntity->id;
                    }
                }else{
                    $task_category_id = 0;
                }

                $description = ($row[5] === '\N') ? '' : htmlentities($row[5]);

                //contructing data for productivities table
                $productivityEntity = $productivitiesTable->newEntity([
                    'staff_id' => $user_id,
                    'client_id' => $client_id,
                    'task_category_id' => $task_category_id,
                    'type' => $row[4],
                    'description' => $description,
                    'status' => $row[6],
                    'created' => $row[7],
                    'modified' => $row[7],
                ]);
                if ($productivitiesTable->save($productivityEntity)) {
                    $ctr++;
                }else{
                    $io->out("Not Save MyStaff productivity_id ".$row[9]);
                }


            }
        }

        $io->out("Done Adding Productivities ". $ctr ." records.\n");
    }
}
