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
class CompleteClientsRecordsCommand extends Command
{
    const COMMAND_NAME = 'Complete Clients Records';
    
    const STATUS_PROCESSED = 'processed';
    
    const STATUS_FAILED = 'failed';
    
    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    
    const STATUS_SUCCESS = 'Success';
    
    //
    
//    private $StaffsTable;
//    
//    private $SystemStaffPartialsTable;
    
    private $ClientsTable;
    
    private $SystemClientPartialsTable;
    
    public $components = ['Token'];
    
    private $ConfigurationsTable;
    
    private $endpoint;
    
    private $endpoint_name;

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

//        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->ClientsTable = TableRegistry::getTableLocator()->get('Clients');
        
//        $this->SystemStaffPartialsTable = TableRegistry::getTableLocator()->get('SystemStaffPartials');
        $this->SystemClientPartialsTable = TableRegistry::getTableLocator()->get('SystemClientPartials');
        
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint_name = 'client/details.json';
        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD) . $this->endpoint_name;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $clients = $this->ClientsTable->getAll();
        
//        debug('IN PROGRESS...');
//        debug($clients);
//        exit();
        
        Log::info("----------------------------------------------------------------------------------------------------");
        Log::info(self::COMMAND_NAME . " Started");
        
        $countProcessed = 0;
        $countFailed = 0;
        foreach($clients as $client){
            $proceedProcessing = false;
            $newSystemClientPartial = false;
            $successfulMigration = self::STATUS_FAILED;
            $remarks = NULL;
            
            if($client->system_client_partial){
                if($client->system_client_partial->status == self::STATUS_PROCESSED){
                    $proceedProcessing = false; //already been processed
                }else{
                    $proceedProcessing = true; //has system partail record, may have been failed before
                }
            }else{
                //no system partial record
                $newSystemClientPartial = true;
                $proceedProcessing = true;
            }
            
            if($proceedProcessing){
                $token = new Token();
                $url = $this->endpoint;
                $response = $this->http->get($url, ['client_code' => $client->short_code], ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);
                $result = $response->getJson();
                
                if ($result['status'] == self::STATUS_SUCCESS){

                    $clientEntity = $this->ClientsTable->find()->where(['short_code' => $client->short_code])->first();
                    $clientData = [
                        'name' => $result['name'],
                    ];

                    if($clientEntity){
                        $clientEntity = $this->ClientsTable->patchEntity($clientEntity, $clientData);
                     }
                    if ($this->ClientsTable->save($clientEntity)) {
                        $successfulMigration = self::STATUS_PROCESSED;//successful migration
                        $countProcessed++;
                    }else{
                        $remarks = 'An error occured saving';
                        $successfulMigration = self::STATUS_FAILED;
                        $countFailed++;

                        $this->log(self::COMMAND_NAME . ': Error occurred when trying to save data. ' . $client->short_code . ' ' . $clientEntity->getErrors());
                    }
                }else{
                    //CMS username name can not be found in UMS/SC
                    $remarks = $result['message'];
                    $successfulMigration = self::STATUS_FAILED;
                    $countFailed++;

                    $this->log(self::COMMAND_NAME . ': Name = ' . $client->name . ' - ' . self::STATUS_FAILED . ' - ' . $result['message']);
                }
                
                $systemClientPartialEntity = $this->SystemClientPartialsTable->find()->where(['client_id' => $client->id])->first();
                
                $systemClientPartialData = [
                    'client_id' => $client->id,
                    'status' => $successfulMigration,
                    'remarks' => $remarks,
                ];
                if ($systemClientPartialEntity) {
                    $systemClientPartialEntity = $this->SystemClientPartialsTable->patchEntity($systemClientPartialEntity, $systemClientPartialData);
                } else {
                    $systemClientPartialEntity = $this->SystemClientPartialsTable->newEntity($systemClientPartialData);
                }
                if($this->SystemClientPartialsTable->save($systemClientPartialEntity)){
                    
                }else{
                    $this->log(self::COMMAND_NAME . ': Error occurred when trying to save data. ' . $client->short_code . ' ' . $systemClientPartialEntity->getErrors());
                }
                
                if($successfulMigration==self::STATUS_PROCESSED){
                    Log::info(self::COMMAND_NAME . ': ' . $client->short_code . ' - ' . $successfulMigration . ((!empty($remarks)) ? ' - ' . $remarks : ''));
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
