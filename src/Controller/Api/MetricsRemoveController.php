<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/MetricsRemove Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsRemove[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MetricsRemoveController extends AppController
{
    const DELETED = 'yes';

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
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->metricsTable = TableRegistry::getTableLocator()->get('Metrics');
        $this->systemLogsTable = TableRegistry::getTableLocator()->get('SystemLogs');
    }

    /**
     * @api {post} /api/metrics/remove.json Remove metric
     * @apiGroup Metrics
     * @apiDescription Remove metric
     * @apiParam {Integer} metric_id The metric id **Required field**
     * @apiParam {String} performed_by The emal of the user who performed the action **Required field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Metric was successfully removed.",
     *  }
     * @apiError {String} status Status
     * @apiError {String} message error message
     * @apiErrorExample {json} Incorrect Category ID:
     * {
     *     "status": "Error",
     *     "message": "Something went wrong. Metric was not removed."
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

        $metiricValue = $this->metricsTable->get($this->request->getData('metric_id'));

        $metiricValue->is_deleted = self::DELETED;

        if ($this->metricsTable->save($metiricValue)) {
            $dataLog = [
                'performed_by' => trim($this->request->getData('performed_by')),
                'affected_entity' => $this->metricEntity->id,
                'log' => 'Remove the metric. Metric id is '.$this->metricEntity->id,
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
                'message' => 'Metric was successfully removed.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Data was not removed.',
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
    }
}
