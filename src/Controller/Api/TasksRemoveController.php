<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/TasksRemove Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsRemove[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TasksRemoveController extends AppController
{
    const IS_DELETED = 'yes';

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
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->tasksTable = TableRegistry::getTableLocator()->get('Tasks');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');

    }

    /**
     * @api {post} /api/tasks/remove.json Remove Tasks
     * @apiGroup Tasks
     * @apiDescription Remove Tasks
     * @apiParam {String} task_id The task id **Required field**
     * @apiParam {String} performed_by The emal of the user who performed the action **Required field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Task was successfully removed.",
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

        $task = $this->tasksTable->patchEntity($this->taskEntity,['is_deleted' => self::IS_DELETED]);

        if ($this->tasksTable->save($task)) {
            $dataLog = [
                'performed_by' => trim($this->request->getData('performed_by')),
                'affected_entity' => $this->taskEntity->id,
                'log' => 'Remove the task. Task id is '.$this->taskEntity->id,
            ];

            $systemLogEntity = $this->systemLogsTable->newEntity($dataLog);
            if (!$this->systemLogsTable->save($systemLogEntity)) {
                $this->log('Error occurred when trying to save in system log histories.');
                $this->log($this->request->getData());
                $this->log($systemLogEntity->getErrors());
            }

            $this->set([
                'status' => 'Success',
                'message' => 'Task was successfully removed.',
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
    }
}
