<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/TasksRemove Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsRemove[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TasksUpdateController extends AppController
{
    const IS_DELETED = 'yes';
    const TYPE_ALL = 'all';
    const TYPE_TEAM = 'team';
    const TYPE_STAFF = 'staff';
    const ASSIGN_TYPE_ALL = 1;
    const ASSIGN_TYPE_TEAM = 2;
    const ASSIGN_TYPE_STAFF = 3;

    /**
     * Task Categories table
     * @var App\Model\Table\MetricsTable
     */
    private $tasksTable;

    /**
     * Task Category entity
     * @var App\Model\Entity\MetricCategory
     */
    private $taskEntity;

    /**
     * Task Categories table
     * @var App\Model\Table\SystemLogsTable
     */
    private $systemLogsTable;

    /**
     * Task Category entity
     * @var App\Model\Entity\SystemLog
     */
    private $systemLogEntity;

    /**
     * Staff
     */
    private $assignsID;

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

        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');
        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->updateParam = [];

    }

    /**
     * @api {post} /api/tasks/update.json Update Tasks
     * @apiGroup Tasks
     * @apiDescription Update the Task Information
     *
     * **type** parameter can either be of the following values:
     *
     * * all - which denotes metrics is for all staff
     * * team - denotes that metric being created is for team or teams
     * * staff - denotes that metric being created is for staff or staffs
     *
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
     * @apiParam {String} task_id The task id **Required field**
     * @apiParam {String} performed_by The username of the user who performed the action **Required field**
     * @apiParam {String} name The name of the task **Optional**
     * @apiParam {String} type The task type. Can be any of the following (all, team, staff) **Optional**
     * @apiParam {String} assign_to Rely's on type param **Optional**
     * @apiParam {String} description The task description **Optional**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Task updated successfully",
     *  }
     * @apiError {String} status Status
     * @apiError {String} message error message
     * @apiErrorExample {json} Incorrect Tasks ID:
     * {
     *     "status": "Error",
     *     "message": "Something went wrong. Task was not removed."
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

        $taskValue = $this->tasksTable->get($this->request->getData('task_id'));
        if (!empty(trim($this->request->getData('name')))) {
          array_push($this->updateParam, ['name' => $this->taskEntity->name . ' to ' . $this->request->getData('name')]);
          $taskValue->name = trim($this->request->getData('name'));

        }
        if (!empty(trim($this->request->getData('type')))) {
          array_push($this->updateParam, ['assignee_type' => $this->taskEntity->assignee_type . ' to ' . $this->assignType, 'assignee_id' => $this->taskEntity->assignee_id . ' to ' . $this->assignsID]);
          $taskValue->assignee_type = $this->assignType;
          $taskValue->assign_id = $this->assignsID;
        }

        if (!empty(trim($this->request->getData('description')))) {
          array_push($this->updateParam, ['description' => $this->taskEntity->description . ' to ' . trim($this->request->getData('description'))]);
          $taskValue->description = trim($this->request->getData('description'));
        }

        if ($this->tasksTable->save($taskValue)) {
            $dataLog = [
                'performed_by' => trim($this->request->getData('performed_by')),
                'affected_entity' => $taskValue->id,
                'log' => 'Update the task information',
                'remarks' => 'Updating ' . $taskValue->id . ' with the following ' . json_encode($this->updateParam) ,
            ];
            $systemLogEntity = $this->systemLogsTable->newEntity($dataLog);
            if (!$this->systemLogsTable->save($systemLogEntity)) {
                $this->log('Error occurred when trying to save update task histories.');
                $this->log($this->request->getData());
                $this->log($systemLogEntity->getErrors());
            }

            $this->set([
                'status' => 'Success',
                'message' => 'Task updated successfully .',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Task was not removed.',
                '_serialize' => ['status', 'message']
            ]);
        }

        return;

    }

    /**
     * Perform sanity checking and validation on required params
     * @return array
     */
    private function validateRequests()
    {
        if (empty(trim($this->request->getData('task_id')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Task ID cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('performed_by')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Performed cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        $this->taskEntity = $this->tasksTable->find()->where(['id' => trim($this->request->getData('task_id')), 'is_deleted !=' => self::IS_DELETED])->first();
        if (empty($this->taskEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Task ID not found',
                '_serialize' => ['status', 'message']
            ];
        }

        if (!empty(trim($this->request->getData('type')))) {
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
              $this->assignsID = '';
            }
        }
    }
}
