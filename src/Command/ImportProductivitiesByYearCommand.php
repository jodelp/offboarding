<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;

/**
 * Import Productivities By Year command.
 */
class ImportProductivitiesByYearCommand extends Command
{
    const IMPORT_DIR_PATH = 'Imports';

    private $importFiles;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->importFiles = [
            '2017' => '2017_productivities.csv',
            '2018' => 'here for 2018',
        ];
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        if (empty($args->getArgumentAt(0))) {
            return $io->error('Year parameter is required.');
        }

        $year = $args->getArgumentAt(0);
        $importFilePath = CONFIG.self::IMPORT_DIR_PATH.DS.$this->importFiles[$year];

        $clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $productivitiesTable = TableRegistry::getTableLocator()->get('Productivities');

        /**
         * Headers as follows
         *
         * 0 Id
         * 1 Email
         * 2 AlternateEmail
         * 3 Client name
         * 4 Category
         * 5 Category name
         * 6 created date
         * 7 type
         * 8 description
         * 9 status
         *
         */
        $fileHandler = fopen($importFilePath, 'r');
        while (($row = fgetcsv($fileHandler)) !== FALSE) {
            // check that client namen is not empty
            if (empty($row[3])) {
                return $io->error('Empty client name was encountered, stopping import now');
            }

            // skip null client name
            if ($row[3] === '\N') {
                continue;
            }

            /**
             * set the username here
             */
            $username = '';
            if (empty($row[1])) {
                $username = $row[2];
            } else {
                $username = $row[1];
            }

            // check that username is not empty
            if (empty($username)) {
                return $io->error('Empty username was encountered, stopping import now');
            }

            // skill null username
            if ($username == '\N') {
                continue;
            }

            // establish client entity here
            $client = $clientsTable->establish($row[3]);

            /**
             * prepare the task category
             */
            if (empty($row[4])) {
                $category = 0;
            } elseif ($row[4] == 1) {
                $category = 1;
            } else {
                $categoryName = $row[5];
                $categoryEntity = $taskCategoriesTable->establish($categoryName, $client);
                $category = $categoryEntity->id;
            }

            // establish staff entity here
            $staff = $staffsTable->establish($username);
            $date = $row[6];

            // sanitize description data
            $description = ($row[8] === '\N') ? '' : htmlentities($row[8]);

            $entity = $productivitiesTable->newEntity([
                'staff_id' => $staff->id,
                'client_id' => $client->id,
                'task_category_id' => $category,
                'type' => $row[7],
                'description' => $description,
                'status' => $row[9],
                'created' => $date,
                'modified' => $date
            ]);

            if (!$productivitiesTable->save($entity)) {
                $this->log($entity);
                $this->log($row);
                $this->log($entity->getErrors());
            }
        }
    }
}