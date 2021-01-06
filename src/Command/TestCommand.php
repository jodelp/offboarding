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
class TestCommand extends Command
{
    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadModel('MystaffUsers');

//        $this->mystaffTasksTable = TableRegistry::getTableLocator()->get('MystaffTasks');
//        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
//        $this->mystaffTeamsTable = TableRegistry::getTableLocator()->get('MystaffTeams');
//        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
//        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');
//        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
//        $this->mystaffTaskAssignTable = TableRegistry::getTableLocator()->get('MystaffTaskAssignees');
//        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
//        $this->http = new Client();
//        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
//        $this->endpoint_name = 'client/details.json';
//        $this->endpoint_team = 'team/get.json';
//        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD);
    }

    /**
     *
     * Execute the command for import_metrics
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out("Done Processing.");
        $this->MystaffUsers->getUsersDetails(12);
    } // end of execute

}
