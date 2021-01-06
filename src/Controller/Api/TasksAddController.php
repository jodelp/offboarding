<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

class TasksAddController extends AppController
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
     * Task Category Entity
     * @var App\Model\Entity\TaskCategory
     */
    private $taskCategoryEntity;


    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');
        $this->taskCategoryTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $this->assignType = '';
    }

    /**
     * @api {post} /api/tasks/add.json Add Tasks Information
     * @apiName Tasks
     * @apiGroup Tasks
     * @apiDescription Add new tasks information for certain client
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
     * @apiParam {String} name The task Name
     * @apiParam {String} task_category The task Category
     * @apiParam {String} type The task type can be any of the ff: (all, team, staff)
     * @apiParam {String} assign_to Rely on the type param.
     * @apiParam {String} description The task description (optional)
     * @apiParam {String} performed_by The user who performed the activity (required)
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} client_code The client short code
     * @apiSuccess {String} name The client name
     * @apiSuccess {Integer} task_id The task id
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *  {
     *     "status": "Success",
     *     "message": "Staff activity report.",
     *     "client_code": "pts",
     *     "name": "Pts Managed Services LTD",
     *     "task_id": 2
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
            'task_category_id' => $this->taskCategoryEntity->id,
        ];

        $tasks = $this->tasksTable->newEntity($data);
        if (!empty(trim($this->request->getData('description')))) {
          $tasks->description = $this->request->getData('description');
        }

        if (!empty(trim($this->request->getData('assign_to')))) {
             $tasks->assign_id = $this->assignsID;
        }

        if ($this->tasksTable->save($tasks)) {
          $dataLogs = [
              'performed_by' => $this->request->getData('performed_by'),
              'affected_entity' => $this->tasksTable->save($tasks)->id,
              'log' => "Create new task. Task id is ". $this->tasksTable->save($tasks)->id,
          ];
          $systemHistory = $this->systemLogsTable->newEntity($dataLogs);
          if (!$this->systemLogsTable->save($systemHistory)) {
              $this->log('Error occurred when trying to save task system logs.');
              $this->log($this->request->getData());
              $this->log($systemLogEntity->getErrors());
          }
            $this->set([
                'status' => 'Success',
                'message' => 'Successfully created a new task',
                'client_code' => $this->clientEntity->short_code,
                'name' => $this->clientEntity->name,
                'task_id' => $this->tasksTable->save($tasks)->id,
                '_serialize' => ['status', 'message', 'client_code', 'name', 'task_id']
            ]);
        } else {
            $this->log('Error occurred when trying to save new tasks record.');
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
                'message' => 'Validation, Task Name cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('task_category')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Task Category Code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
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

        $conditions = [
            'name' => trim($this->request->getData('task_category')),
            'OR' => [
                ['client_id' => $this->clientEntity->id,],
                ['client_id' => 0],
            ]
        ];

        $this->taskCategoryEntity = $this->taskCategoryTable->find()->where($conditions)->first();

        if (empty($this->taskCategoryEntity)) {
          return [
              'status' => 'Error',
              'message' => 'Validation, No Task Category Found',
              '_serialize' => ['status', 'message']
          ];
        }


    }
}
