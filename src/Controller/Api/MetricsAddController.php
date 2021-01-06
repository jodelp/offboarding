<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

class MetricsAddController extends AppController
{
    const TYPE_ALL = 'all';
    const TYPE_TEAM = 'team';
    const TYPE_STAFF = 'staff';
    const ASSIGN_TYPE_ALL = 1;
    const ASSIGN_TYPE_TEAM = 2;
    const ASSIGN_TYPE_STAFF = 3;

    /**
     * Staff
     */
    private $assignsID;

    /**
     * Staff Entity
     * @var App\Model\Entity\Staff
     */
    private $staffEntity;

    /**
     * assignee_type
     */
    private $assignType;


    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->metricsTable = TableRegistry::getTableLocator()->get('Metrics');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');
        $this->assignType = '';
    }

    /**
     * @api {post} /api/metrics/add.json Add Metrics Information
     * @apiGroup Metrics
     * @apiDescription Creates a new metrics information for certain client indicated by client code.
     *
     * **type** parameter can either be of the following values:
     *
     * * all - which denotes metrics is for all staff
     * * team - denotes that metric being created is for team or teams
     * * staff - denotes that metric being created is for staff or staffs
     *
     * **assign_to** expected values
     *
     * 1. If type param is set to "all" assign_to can be left blank
     * 2. If type param is set to "team" assign_to expects team_id or team_id's for multiple team comma delimited. Example below
     *
     *      assign_to = 25 or (team_id)
     *
     *      assign_to = 25, 125, 246  (team_id's)
     *
     * 3. If type param is set to "staff" assign_to expects staff or staff's username comma delimited.
     *
     *      assign_to = allana@cloudstaff.com (staff username)
     *
     *      assign_to = allana@cloudstaff.com, kristelm@cloudstaff.com, crisp@cloudstaff.com (staff usernames)
     *
     * @apiParam {String} client_code Client ShortCode
     * @apiParam {String} name The metric Name
     * @apiParam {String} type The metric type can be any of the ff: (all, team, staff)
     * @apiParam {String} assign_to Rely on the type param.
     * @apiParam {String} description The metric description (optional)
     * @apiParam {String} performed_by The user who performed the activity (reuired)
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} client_code The client short code
     * @apiSuccess {String} name The client name
     * @apiSuccess {String} metric_id The metric id
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *  {
     *     "status": "Success",
     *     "message": "Staff activity report.",
     *     "client_code": "pts",
     *     "name": "Pts Managed Services LTD",
     *     "metric_id": 2
     * }
     *
     */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }
        $data = [
            'client_id' => $this->clientEntity->id,
            'assignee_type' => $this->assignType,
            'name' => $this->request->getData('name'),
        ];

        $metrics = $this->metricsTable->newEntity($data);
        if (!empty(trim($this->request->getData('description')))) {
          $metrics->description = $this->request->getData('description');
        }

        if (!empty(trim($this->request->getData('assign_to')))) {
             $metrics->assign_id = $this->assignsID;
        }

        if ($this->metricsTable->save($metrics)) {
          $dataLogs = [
              'performed_by' => $this->request->getData('performed_by'),
              'affected_entity' => $this->metricsTable->save($metrics)->id,
              'log' => "Create new metric. Metric id is ". $this->metricsTable->save($metrics)->id,
          ];
          $systemHistory = $this->systemLogsTable->newEntity($dataLogs);
          if (!$this->systemLogsTable->save($systemHistory)) {
              $this->log('Error occurred when trying to save metrics system logs.');
              $this->log($this->request->getData());
              $this->log($systemLogEntity->getErrors());
          }
            $this->set([
                'status' => 'Success',
                'message' => 'Successfully created a new metric',
                'client_code' => $this->clientEntity->short_code,
                'name' => $this->clientEntity->name,
                'metric_id' => $this->metricsTable->save($metrics)->id,
                '_serialize' => ['status', 'message', 'client_code', 'name', 'metric_id']
            ]);
        } else {
            $this->log('Error occurred when trying to save new productivity record.');
            $this->log($this->request->getData());
            $this->log($entity->getErrors());
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
    private function validateRequests()
    {

        if (empty(trim($this->request->getData('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client Code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('name')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Metric Name cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('name')))) {
            $this->staffEntity = $this->StaffsTable->establish($username, false);
        }

        if (empty(trim($this->request->getData('type')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Type cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (trim($this->request->getData('type')) != self::TYPE_ALL && trim($this->request->getData('type')) != self::TYPE_TEAM && trim($this->request->getData('type')) != self::TYPE_STAFF ) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Invalid Type (either of the ff: all, team, staff)',
                '_serialize' => ['status', 'message']
            ];
        }

        if (trim($this->request->getData('type')) != self::TYPE_ALL) {
          if (empty(trim($this->request->getData('assign_to')))) {
              return [
                  'status' => 'Error',
                  'message' => 'Validation, Assign to cannot be blank',
                  '_serialize' => ['status', 'message']
              ];
          }
          if (trim($this->request->getData('type')) == self::TYPE_TEAM) {
            $this->assignType = self::ASSIGN_TYPE_TEAM;
            $this->assignsID = $this->request->getData('assign_to');
          }else {
            /**
             * validate staff if in our records
             */
             $staffs = explode(',', $this->request->getData('assign_to'));
             $assignTo = [];
             foreach ($staffs as $staff) {
               $staffEntity = $this->StaffsTable->establish($staff);
               array_push($assignTo, $staffEntity->id);
             }
             $this->assignsID = implode(',', $assignTo);
             $this->assignType = self::ASSIGN_TYPE_STAFF;
          }
        } else {
          $this->assignType = self::ASSIGN_TYPE_ALL;
        }

        if (empty(trim($this->request->getData('performed_by')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Performed by cannot be blank',
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

    }
}
