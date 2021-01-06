<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use Cake\Http\Client;
use App\Service\Token;

/**
 * Api/IdleInterval Controller
 *
 *
 * @method \App\Model\Entity\Api/IdleInterval[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IdleIntervalController extends AppController
{
    const LOGCENTRAL_URL = 'logcentral-url';

    /**
     * Staff Entity
     * @var App\Model\Entity\Staff
     */
    private $staffEntity;

    /**
     * Client Entity
     * @var App\Model\Entity\Client
     */
    private $clientEntity;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->http = new Client();
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->logCentralUrl = $this->ConfigurationsTable->getConfig(self::LOGCENTRAL_URL);
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->workbenchSettingsTable = TableRegistry::getTableLocator()->get('WorkbenchSettings');
    }

    /**
     * @api {post} /api/idle_interval.json Assign idle interval for staff
     * @apiName Assign idle interval for staff
     * @apiDescription Assign Idle Time starts for staff.
     *
     * @apiGroup Workbench Settings
     * @apiParam {String} username This is the Staff Username. **Required Field**
     * @apiParam {String} client_code This is the Client Short Code. **Required Field**
     * @apiParam {String} interval_value Idle time starts after (expected value in seconds) **Required Field**
     * @apiParam {String} performed_by Username of the staff who performed the activity **Required Field**
     * @apiParam {String} performed_by_fullname Fullname of the staff who performed the activity **Required Field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Workbench settings were successfully saved.",
     *  }
     */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateEditRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }
        $entity = $this->workbenchSettingsTable->find()->where(['staff_id' => $this->staffEntity->id, 'client_id' => $this->clientEntity->id])->first();
        if ($entity) {
          $dataLog = [
              'path' => $this->request->getRequestTarget(),
              'remarks' => "Updated Idle Time starts after in Workbench Settings",
              'performed_by' => $this->request->getData('performed_by'),
              'performed_by_fullname' => $this->request->getData('performed_by_fullname'),
              'transaction_type' => "Update",
              'client_name' => $this->clientEntity->name,
              'shortcode' => $this->clientEntity->short_code,
              'payload' => serialize(json_encode(['idle_time_starts_after' => $this->request->getData('interval_value')])),
              'prev_payload' => serialize(json_encode(['idle_time_starts_after' => $entity->idle_time_starts_after])),
              'affected_user' => $this->staffEntity->username,
              'affected_user_fullname' => $this->staffEntity->first_name . $this->staffEntity->last_name,
          ];
          $workbenchSettingsEntity = $this->workbenchSettingsTable->patchEntity($entity, ['idle_time_starts_after' => $this->request->getData('interval_value')]);
        } else {
          $data = [
            'staff_id' => $this->staffEntity->id,
            'client_id' => $this->clientEntity->id,
            'idle_time_starts_after' => $this->request->getData('interval_value'),
          ];
          $workbenchSettingsEntity = $this->workbenchSettingsTable->newEntity($data);
          $dataLog = [
              'path' => $this->request->getRequestTarget(),
              'remarks' => "Added new Idle Time starts after in Workbench Settings",
              'performed_by' => $this->request->getData('performed_by'),
              'performed_by_fullname' => $this->request->getData('performed_by_fullname'),
              'transaction_type' => "Create",
              'client_name' => $this->clientEntity->name,
              'shortcode' => $this->clientEntity->short_code,
              'payload' => serialize(json_encode($data)),
              'affected_user' => $this->staffEntity->username,
              'affected_user_fullname' => $this->staffEntity->first_name . $this->staffEntity->last_name,
          ];
        }

        if ($this->workbenchSettingsTable->save($workbenchSettingsEntity)) {
          $token = new Token();
          $this->http->post($this->logCentralUrl . 'log.json', $dataLog, ['headers' => ['Authorization' => 'Bearer '.$token->getToken()]]);

            $this->set([
                'status' => 'Success',
                'message' => 'Workbench settings were successfully saved.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->log('Error occurred when trying to save client workbench settings.');
            $this->log($workbenchSettingsEntity->getErrors());
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Data was not saved.',
                '_serialize' => ['status', 'message']
            ]);
        }

      }

    /**
     * Perform sanity checking and validation on required params
     * @return array
     */
    private function validateEditRequests()
    {
        if (empty(trim($this->request->getData('username')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Username cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('interval_value')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Interval value cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('performed_by')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Performed By cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('performed_by_fullname')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Performed By Fullname cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        /**
         * establish the staff entity
         */
        $this->staffEntity = $this->staffsTable->establish($this->request->getData('username'));
        if (empty($this->staffEntity)) {
            return [
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate staff username. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ];
            return;
        }

        $this->clientEntity = $this->clientsTable->find()->where(['short_code' => $this->request->getData('client_code')])->first();
        if (empty($this->clientEntity)) {
            return [
                'status' => 'Internal Error Occured',
                'message' => 'No client found',
                '_serialize' => ['status', 'message']
            ];
            return;
        }

    }


}
