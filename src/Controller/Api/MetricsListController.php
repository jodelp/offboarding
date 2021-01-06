<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/MetricsList Controller
 *
 *
 * @method \App\Model\Entity\Api/MetricsList[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MetricsListController extends AppController
{
    const TYPE_ALL = 'all';
    const TYPE_TEAM = 'team';
    const TYPE_STAFF = 'staff';
    const ASSIGN_TYPE_ALL = 1;
    const ASSIGN_TYPE_TEAM = 2;
    const ASSIGN_TYPE_STAFF = 3;
    const METRIC_TYPES = [1 => 'all', 2 => 'team', 3 => 'staff'];

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
     * assignee_type
     */
    private $assignType;

    private $clientEntity;
    private $staffEntity;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->metricsTable = TableRegistry::getTableLocator()->get('Metrics');
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');

    }

    /**
     * @api {get} /api/metrics/list.json Metric list of a certain client
     * @apiName Metric list of a certain client
     * @apiDescription Fetch all non deleted metrics of a certain client
     *
     * **Consumed By:**
     *  * MyStaff
     * @apiGroup Metrics
     * @apiParam {String} client_code This is the shortcode of the client **Required**
     * @apiParam {String} type This is the type of the metrics. The metric type can be any of the ff: (all, team, staff)  **Required**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *
     * * Type set to all
     *  HTTP/1.1 200 OK
     *         {
     *        "status": "Success",
     *        "message": "Records found",
     *        "client_name": "Cloudstaff Philippined Inc",
     *        "client_shortcode": "CLOUSTAFF",
     *        "items": [
     *            {
     *                "metric_id": 23,
     *                "name": "Resolve tickets",
     *                "type": "all"
     *            },
     *            {
     *                "metric_id": 13,
     *                "name": "Technical support",
     *                "type": "team",
     *                "assign_to": "2, 12"
     *            },
     *            {
     *                "metric_id": 52,
     *                "name": "Sell items",
     *                "type": "staff",
     *                 "assign_to": "allana@cloudstaff.com"
     *            },
     *            {
     *                "metric_id": 26,
     *                "name": "Customer support",
     *                "type": "staff",
     *                "assign_to": "allana@cloudstaff.com, crisp@cloudstaff.com, kristelm@cloudstaff.com"
     *            }
     *        ]
     *    }
     */
    public function index()
    {
        // validate/sanitize the requests params
        $errors = $this->validateEditRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

        if (trim($this->request->getQuery('type')) == self::TYPE_TEAM) {
            $this->assignType = self::ASSIGN_TYPE_TEAM;
        }
        if (trim($this->request->getQuery('type')) == self::TYPE_STAFF) {
            $this->assignType = self::ASSIGN_TYPE_STAFF;
        }

        $condition = [
            'client_id' => $this->clientEntity->id,
            'is_deleted' => 'no'
        ];

        //add the condition assignee_type if the value of type params is not 'all'
        if (trim($this->request->getQuery('type')) != self::TYPE_ALL) {
            $condition['assignee_type'] = $this->assignType;
        }

        $this->metricEntity = $this->metricsTable->find()->where($condition)->all();
        $data = [];
        foreach ($this->metricEntity as $index => $metric) {
            array_push($data, [
                'metric_id' => $metric->id,
                'name' => $metric->name,
                'type' => self::METRIC_TYPES[$metric->assignee_type],
            ]);

            //add the assign_to field if the assign_type of the entity is not all
            if ($metric->assignee_type != self::ASSIGN_TYPE_ALL) {
                $metricAssignTo = $metric->assign_id;
                //get usernames if the assign_type of the entity is staff
                if ($metric->assignee_type == self::ASSIGN_TYPE_STAFF) {
                    $staffAssign  = [];
                    $metricAssignIds = explode(",", $metric->assign_id);
                    foreach ($metricAssignIds as $key => $assign) {
                        $this->staffEntity = $this->staffsTable->find()->where(['id' => trim($assign)])->first();
                        if (!empty($this->staffEntity)) {
                            array_push($staffAssign, $this->staffEntity->username);
                        }
                    }
                    $metricAssignTo = implode(', ', $staffAssign);
                }
                $data[$index]['assign_to'] = $metricAssignTo;
            }
        }

        if (!empty($data)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Records found.',
                'client_name' => $this->clientEntity->name,
                'client_shortcode' => $this->clientEntity->short_code,
                'items' => $data,
                '_serialize' => ['status', 'message', 'client_shortcode', 'client_name', 'items']
            ]);
        } else {
            $this->set([
                'status' => 'Failed',
                'message' => 'Records not found.',
                'client_name' => $this->clientEntity->name,
                'client_shortcode' => $this->clientEntity->short_code,
                'items' => $data,
                '_serialize' => ['status', 'message', 'client_name', 'client_shortcode', 'items']
            ]);
        }
    }

    /**
     * Perform sanity checking and validation on required params
     * @return array
     */
    private function validateEditRequests()
    {

        if (empty(trim($this->request->getQuery('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        $this->clientEntity = $this->clientsTable->find()->where(['short_code' => $this->request->getQuery('client_code')])->first();

        if (empty($this->clientEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client shortcode not found.',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getQuery('type')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Type cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (!in_array($this->request->getQuery('type'), self::METRIC_TYPES)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Invalid Type (either of the ff: all, team, staff)',
                '_serialize' => ['status', 'message']
            ];
        }

     }
}
