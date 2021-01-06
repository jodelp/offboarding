<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/MetricsUpdate Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsUpdate[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MetricsUpdateController extends AppController
{
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
    private $metricsTable;

    /**
     * Task Category entity
     * @var App\Model\Entity\MetricCategory
     */
    private $metricEntity;

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

        $this->metricsTable = TableRegistry::getTableLocator()->get('Metrics');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');

    }

    /**
    * @api {post} /api/metrics/update.json Update Metrics Information
    * @apiGroup Metrics
    * @apiDescription Update Metrics Information.
    *
    * **Assign_to** is rely on the type param:
    *
    *    if the type is **all** (assign_to value should be blank)
    *
    *    if the type is **team** (assign_to value must reference cms.team_id)
    *
    *    if the type is **staff** (assign_to value must be staff username)
    *
    * @apiParam {String} metric_id The id of the metric entity **Required field**
    * @apiParam {String} performed_by The user who performed the action **Required field**
    * @apiParam {String} name The metric Name (optional)
    * @apiParam {String} type The metric type can be any of the ff: (all, team, staff)
    * @apiParam {String} assign_to Rely on the type param.
    * @apiParam {String} description The metric description (optional)
    * @apiSuccess {String} status Status label of your request, either success or error
    * @apiSuccess {String} message Status message
    *
    * @apiSuccessExample {json} Success-Response:
    * HTTP/1.1 200 OK
    *  {
    *     "status": "Success",
    *     "message": "Metric infromation was successfully updated.",
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

        $enum = 'assignee_type';

        $this->assignType = self::ASSIGN_TYPE_ALL;
        $this->assignsID = '';

        if (trim($this->request->getData('type')) == self::TYPE_TEAM) {
          $this->assignType = self::ASSIGN_TYPE_TEAM;
          $this->assignsID = $this->request->getData('assign_to');
        }

        if (trim($this->request->getData('type')) == self::TYPE_STAFF) {
            $staffs = explode(',', $this->request->getData('assign_to'));
            $assignTo = [];
            foreach ($staffs as $staff) {
              $staffEntity = $this->staffsTable->establish($staff);
              array_push($assignTo, $staffEntity->id);
            }
            $this->assignsID = implode(',', $assignTo);
            $this->assignType = self::ASSIGN_TYPE_STAFF;
        }

        $metiricValue = $this->metricsTable->get($this->request->getData('metric_id'));
        $metiricValue->performed_by = $this->request->getData('performed_by');
        $metiricValue->assignee_type = $this->assignType;

        if (!empty($this->request->getData('name'))) {
            $metiricValue->name = $this->request->getData('name');
            $enum .= ', name';
        }

        if (!empty($this->request->getData('description'))) {
            $metiricValue->description = $this->request->getData('description');
            $enum .= ', description';
        }

        if (!empty($this->request->getData('assign_to'))) {
            $metiricValue->assign_id = $this->assignsID;
            $enum .= ', assign_id';
        }

        if ($this->metricsTable->save($metiricValue)) {
            $dataLog = [
                'performed_by' => trim($this->request->getData('performed_by')),
                'affected_entity' => $this->metricEntity->id,
                'log' => 'Update the metric information',
                'remarks' => 'Updating '.$this->metricEntity->id.' with the following : '.$enum
            ];

            $systemLogEntity = $this->systemLogsTable->newEntity($dataLog);

            $save = $this->systemLogsTable->save($systemLogEntity);

            if (!$save) {
                $this->log('Error occurred when trying to save metrics system log.');
                $this->log($this->request->getData());
                $this->log($systemLogEntity->getErrors());
            }

            $this->set([
                'status' => 'Success',
                'message' => 'Metric infromation was successfully updated.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Data was not saved.',
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
        if (empty(trim($this->request->getData('metric_id')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Metric ID cannot be blank',
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

        $this->metricEntity = $this->metricsTable->find()->where(['id' => trim($this->request->getData('metric_id'))])->first();

        if (empty($this->metricEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Metric ID not found',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty($this->request->getData('type'))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Type cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (in_array($this->request->getData('type'), ['team', 'staff']) && empty($this->request->getData('assign_to'))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Assign to cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (!in_array($this->request->getData('type'), ['team', 'staff', 'all'])) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Invalid Type (either of the ff: all, team, staff)',
                '_serialize' => ['status', 'message']
            ];
        }
    }
}
