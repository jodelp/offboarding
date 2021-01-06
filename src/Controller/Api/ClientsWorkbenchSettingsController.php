<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

class ClientsWorkbenchSettingsController extends AppController
{

    
    private $clientEntity;
    
    private $clientWorkbenchsettingsEntity;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->clientsWorkbechSettingsTable = TableRegistry::getTableLocator()->get('ClientsWorkbenchSettings');
        
    }

    /**
    * @api {post} /api/clients/workbench_settings.json Save client default workbench settings
    * @apiName Save client default workbench settings
    * @apiGroup Workbench Settings
    * @apiDescription **Save client default workbench settings**
    * 
    * Expected values should be *minutes* but will be converted and saved in *seconds*
    * 
    * *screenshot_interval*
    * 
    *   *   input = 30
    * 
    *   *   saved value = 1800
    *  
    * *idle_time*
    * 
    *   *   input = 5
    * 
    *   *   saved value = 300
    * 
    * @apiParam {String} client_code The client code **Required field**
    * @apiParam {String} screenshot_interval Screen shot interval (expected value in minutes e.g. 30)  **Required field**
    * @apiParam {String} idle_time Idle time starts after  (expected value in minutes e.g. 5)  **Required field**
    * @apiSuccess {String} status Status
    * @apiSuccess {String} message Status message
    * @apiSuccessExample {json} Success-Response:
    * {
    *     "status": "Success",
    *     "message": "Client workbench settings were successfully saved."
    * }
    */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateEditRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }
        
        $data = [
            'client_id' => $this->clientEntity->id,
            'screen_capture' => intval($this->request->getData('screenshot_interval')) * 60,
            'idle_time_starts_after' => intval($this->request->getData('idle_time')) * 60,
        ];
        
        $this->clientWorkbenchsettingsEntity = $this->clientsWorkbechSettingsTable->getWorkbenchSettingsByClientId($this->clientEntity->id);
        
        if ($this->clientWorkbenchsettingsEntity) {
            $this->clientWorkbenchsettingsEntity = $this->clientsWorkbechSettingsTable->patchEntity($this->clientWorkbenchsettingsEntity, $data);
        } else {
            $this->clientWorkbenchsettingsEntity = $this->clientsWorkbechSettingsTable->newEntity($data);
        }
        
        if ($this->clientsWorkbechSettingsTable->save($this->clientWorkbenchsettingsEntity)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Client workbench settings were successfully saved.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->log('Error occurred when trying to save client workbench settings.');
            $this->log($data);
            $this->log($this->clientWorkbenchsettingsEntity->getErrors());
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Data was not saved.',
                '_serialize' => ['status', 'message']
            ]);
        }
        
        

    }
    
    private function validateEditRequests()
    {
        if (empty(trim($this->request->getData('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }
        
        if (empty(trim($this->request->getData('screenshot_interval')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Screenshot interval cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }
        
        if (empty(trim($this->request->getData('idle_time')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Idle time cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }
        
        /**
         * establish the client entity
         */
        $this->clientEntity = $this->clientsTable->establish($this->request->getData('client_code'), $this->request->getData('client_code'));
        if (empty($this->clientEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate client name. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
        }
        
        return [];

    }


   

}
