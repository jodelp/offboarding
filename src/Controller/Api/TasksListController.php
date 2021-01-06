<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/TasksList Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsRemove[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TasksListController extends AppController
{
    const IS_DELETED = 'yes';
    const TYPE_ALL = 'all';
    const TYPE_TEAM = 'team';
    const TYPE_STAFF = 'staff';
    const ASSIGN_TYPE_ALL = 1;
    const ASSIGN_TYPE_TEAM = 2;
    const ASSIGN_TYPE_STAFF = 3;
    const TYPES = [1 => 'all', 2 => 'team', 3 => 'staff'];

    /**
     * Task Category entity
     * @var App\Model\Entity\MetricCategory
     */
    private $taskEntity;

    /**
     * Client entity
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
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');

    }

    /**
     * @api {post} /api/tasks/list.json List all tasks for certain client
     * @apiGroup Tasks
     * @apiDescription List all tasks for certain client.
     *
     * The default **type** value is all.
     *
     * **type** can be of the following values:
     *  1. all
     *  2. team
     *  3. staff
     *
     * @apiParam {String} client_code The client shortcode **Required field**
     * @apiParam {String} type The task type can be the following: all, staff, team.
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Records found",
     *      "client_name": "Cloudstaff Philippined Inc",
     *      "client_shortcode": "CLOUSTAFF",
     *      "items": [
     *          {
     *            "task_id": 23,
     *            "name": "Resolve tickets",
     *            "type": "all",
     *            "category": "General"
     *          },
     *      ]
     *  }
     * @apiError {String} status Status
     * @apiError {String} message error message
     * @apiErrorExample {json} Incorrect Client Code:
     * {
     *     "status": "Error",
     *     "message": "Something went wrong. Client code not in our record."
     * }
     */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

        $results = [];
        foreach ($this->taskEntity as $task) {
          $data = [
            'task_id' => $task->id,
            'name' => $task->name,
            'type' => self::TYPES[$task->assignee_type],
          ];

          if ($task->assignee_type == self::ASSIGN_TYPE_TEAM) {
            $data['type'] = self::TYPE_TEAM;
            $data['assign_to'] = $task->assign_id;

          }
          if ($task->assignee_type == self::ASSIGN_TYPE_STAFF) {
            $staffs = explode(',', $task->assign_id);
            $assignTo = [];
            foreach ($staffs as $staff) {
              $staffEntity = $this->staffsTable->find()->where(['id' => $staff])->first();
              array_push($assignTo, $staffEntity->username);
            }
            $data['type'] = self::TYPE_STAFF;
            $data['assign_to'] = implode(',', $assignTo);
          }
          $data['category'] = $task->task_category->name;
           array_push($results, $data);
        }

        $this->set([
            'status' => 'Success',
            'message' => 'Records Found',
            'client_name' => $this->clientEntity->name,
            'client_shortcode' => $this->clientEntity->short_code,
            'items' => $results,
            '_serialize' => ['status', 'message', 'client_name', 'client_shortcode', 'items']
        ]);
        return;
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

        $this->clientEntity = $this->clientsTable->find()->where(['short_code' => trim($this->request->getData('client_code'))])->first();

        if (empty($this->clientEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Client not found',
                '_serialize' => ['status', 'message']
            ];
        }


        $conditions = [
          'Tasks.client_id' => $this->clientEntity->id,
          'Tasks.is_deleted !=' => self::IS_DELETED,
        ];
        if (!empty(trim($this->request->getData('type')))) {
          if (!in_array($this->request->getData('type'), self::TYPES)) {
              return [
                  'status' => 'Error',
                  'message' => 'Validation, Invalid Type (either of the ff: all, team, staff)',
                  '_serialize' => ['status', 'message']
              ];
          }
          if ($this->request->getData('type') == self::TYPE_TEAM) {
              $conditions['Tasks.assignee_type'] = self::ASSIGN_TYPE_TEAM;

          }
          if ($this->request->getData('type') == self::TYPE_STAFF) {
              $conditions['Tasks.assignee_type'] = self::ASSIGN_TYPE_STAFF;
          }
        }

        $this->taskEntity = $this->tasksTable->find()->contain('TaskCategories')->where($conditions)->all();
        if (empty($this->taskEntity->toArray())) {
          return [
              'status' => 'Error',
              'message' => 'No Task Found',
              '_serialize' => ['status', 'message']
          ];
        }
    }
}
