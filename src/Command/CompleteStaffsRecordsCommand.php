<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use App\Model\Table\ClientManagersTable;
use Cake\Collection\Collection;
use Cake\Log\Log;

use Cake\Http\Client;
use App\Service\Token;


/**
 * This is the migration scrip for clients
 *
 * NOTES:
 *  1. Make sure that the configuration is in place at app.php Datasource and set accordingly
 *
 * 'staffcentral' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => '172.17.0.2',
            'username' => 'root',
            'password' => 'aatim',
            'database' => 'cs_cib',
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,
            'url' => env('DATABASE_URL', null),
        ],
 *
 *
 */
class CompleteStaffsRecordsCommand extends Command
{
    const COMMAND_NAME = 'Complete Staff Records';
    
    const STATUS_PROCESSED = 'processed';
    
    const STATUS_FAILED = 'failed';
    
    const UMS_ENDPOINT_FIELD = 'users-microservice-endpoint';
    
    const STATUS_SUCCESS = 'Success';
    
    //
    
    private $StaffsTable;
    
    private $SystemStaffPartialsTable;
    
    public $components = ['Token'];
    
    private $ConfigurationsTable;
    
    private $endpoint;

    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser->addArgument('name', [
            'help' => 'What is your name'
        ]);
        return $parser;
    }

    /**
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        
        $this->SystemStaffPartialsTable = TableRegistry::getTableLocator()->get('SystemStaffPartials');       
        
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint = $this->ConfigurationsTable->getConfig(self::UMS_ENDPOINT_FIELD);
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $staffs = $this->StaffsTable->getAll();
        
        Log::info("----------------------------------------------------------------------------------------------------");
        Log::info(self::COMMAND_NAME . " Started");
        
        $countProcessed = 0;
        $countFailed = 0;
        foreach($staffs as $staff){
            $proceedProcessing = false;
            $newSystemStaffPartial = false;
            $successfulMigration = self::STATUS_FAILED;
            $remarks = NULL;
            
            if($staff->system_staff_partial){
                if($staff->system_staff_partial->status == self::STATUS_PROCESSED){
                    $proceedProcessing = false; //already been processed
                }else{
                    $proceedProcessing = true; //has system partail record, may have been failed before
                }
            }else{
                //no system partial record
                $newSystemStaffPartial = true;
                $proceedProcessing = true;
            }

            if($proceedProcessing){
                $token = new Token();
                $url = $this->endpoint . 'staff.json';
                $response = $this->http->get($url, ['username' => $staff->username], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
                $result = $response->getJson();

                if ($result['status'] == self::STATUS_SUCCESS) {//successful call to UMS
                    foreach($result['data'] as $record){
                        if(!isset($record['message'])){
                            
                            $staffEntity = $this->StaffsTable->find()->where(['username' => $staff->username])->first();
                            $staffData = [
                                'employee_id' => $record['employee_id'],
                                'last_name' => $record['lastname'],
                                'first_name' => $record['firstname'],
                                'middle_name' => $record['middlename'],
                                'gender' => $record['gender'],
                                'user_status' => $record['user_status'],
                                'employment_provider' => $record['employment_provider'],
                            ];
                            
                            if($staffEntity){
                                $staffEntity = $this->StaffsTable->patchEntity($staffEntity, $staffData);
                             }
                            if ($this->StaffsTable->save($staffEntity)) {
                                $successfulMigration = self::STATUS_PROCESSED;//successful migration
                                $countProcessed++;
                            }else{
                                $remarks = 'An error occured saving';
                                $successfulMigration = self::STATUS_FAILED;
                                $countFailed++;

                                $this->log(self::COMMAND_NAME . ': Error occurred when trying to save data. ' . $staff->username . ' ' . $staffEntity->getErrors());
                            }
                        }else{
                            //CMS username name can not be found in UMS/SC
                            $remarks = $record['message'];
                            $successfulMigration = self::STATUS_FAILED;
                            $countFailed++;
                            
                            $this->log(self::COMMAND_NAME . ': ' . $staff->username . ' - ' . self::STATUS_FAILED . ' - ' . $record['message']);
                            
                        }
                    }
                }else{
                    $remarks = 'An error occured while calling ' . $url;
                    $successfulMigration = self::STATUS_FAILED;
                    $countFailed++;
                    
                    $this->log(self::COMMAND_NAME . ': An error occured while calling ' . $url . ' - ' . $staff->username);
                }
                
                $systemStaffPartialEntity = $this->SystemStaffPartialsTable->find()->where(['staff_id' => $staff->id])->first();
                
                $systemStaffPartialData = [
                    'staff_id' => $staff->id,
                    'status' => $successfulMigration,
                    'remarks' => $remarks,
                ];
                if ($systemStaffPartialEntity) {
                    $systemStaffPartialEntity = $this->SystemStaffPartialsTable->patchEntity($systemStaffPartialEntity, $systemStaffPartialData);
                } else {
                    $systemStaffPartialEntity = $this->SystemStaffPartialsTable->newEntity($systemStaffPartialData);
                }
                if($this->SystemStaffPartialsTable->save($systemStaffPartialEntity)){
                    
                }else{
                    $this->log(self::COMMAND_NAME . ': Error occurred when trying to save data. ' . $staff->username . ' ' . $systemStaffPartialEntity->getErrors());
                }
                
                if($successfulMigration==self::STATUS_PROCESSED){
                    Log::info(self::COMMAND_NAME . ': ' . $staff->username . ' - ' . $successfulMigration . ((!empty($remarks)) ? ' - ' . $remarks : ''));
                }
                
            }
            
            
        }

        Log::info("SUMMARY");
        Log::info("PROCESSED: " . strval($countProcessed));
        Log::info("FAILED: " . strval($countFailed));
        Log::info("TOTAL: " . strval($countProcessed + $countFailed));
        Log::info(self::COMMAND_NAME . " Completed");
        Log::info("----------------------------------------------------------------------------------------------------");
        
    }
}
