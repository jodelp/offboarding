<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/SummaryProductivityReport Controller
 *
 *
 * @method \App\Model\Entity\Api/SummaryProductivity[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SummaryProductivityReportController extends AppController
{

    /**
     * Staffs Table
     * @var App\Model\Table\StaffsTable
     */
    private $mystaffStaffsTable;

    /**
     * Start Shift
     * @var String
     */
    private $shift_start;

    /**
     * End Shift
     * @var String
     */
    private $shift_end;

    /**
     * Staff Timezone
     * @var String
     */
    private $timezone;

    /**
     * Summary Productivities Table
     * @var App\Model\Table\SummaryProductivitiesTable
     */
    private $summaryProductivitiesTable;


      /**
       * initialize method
       * @return void
       */
      public function initialize(): void
      {
          parent::initialize();

          $this->is_database_conn = $this->isDbUp(['mystaff', 'cs_cib']);

          if($this->is_database_conn != 'success') {
            $this->dbname = $this->is_database_conn;
          }

          $this->mystaffProductivitiesTable = TableRegistry::getTableLocator()->get('MystaffProductivities');
          $this->mystaffStaffsTable = TableRegistry::getTableLocator()->get('MystaffStaffs');
          $this->summaryProductivitiesTable = TableRegistry::getTableLocator()->get('SummaryProductivities');
          $this->ScShiftsTable = TableRegistry::getTableLocator()->get('SCShifts');

          $this->dateTimeFormat = 'Y-m-d H:i:s';
          $this->setDefaultTimezone = new \DateTimeZone('Asia/Manila');
          $this->setUTCTimezone = new \DateTimeZone('UTC');

          $this->required_params = ['username', 'request_date'];
      }

      /**
       * @api {post} /api/summary_productivity_report.json Summary productivity of staff
       * @apiName Summary Productivity Report
       * @apiDescription Return the summary productivity of the staff on the indicated request date.
       * @apiGroup Productivity
       * @apiParam {String} username This is the staffs username
       * @apiParam {String} request_date The date request. This date is expected as _**Asia/Manila**_ timezone
       * @apiSuccess {String} status Status label of your request, either success or error
       * @apiSuccess {String} message Status message
       * @apiSuccess {Array} summary Return the summary productivity of the staff
       * @apiSuccess {String} summary.username Username of the staff
       * @apiSuccess {String} summary.client_name Client Name of the staff
       * @apiSuccess {String} summary.request_date Date request of the staff
       * @apiSuccess {String} summary.task Total number of the accomplished task
       * @apiSuccess {String} summary.pending Total number of the pending task
       * @apiSuccess {String} summary.constraints Total number of the constraints
       * @apiSuccess {Array} summary.accomplish_task Return all resolved tasks
       * @apiSuccess {Array} summary.pending_task Return all pending tasks
       * @apiSuccessExample {json} Success-Response:
       *  HTTP/1.1 200 OK
       *  {
       *      "status": "Success",
       *      "message": "Summary Productivity Report",
       *      "summary": {
       *          "username": "test@cloudstaff.com",
       *          "client_name": "CloudStaff Philippines",
       *          "request_date": 2020-02-11,
       *          "task": 1,
       *          "pending": 1,
       *          "constraints": 0,
       *          "accomplish_task": {
       *              "task_category": "General",
       *              "name": "Task1",
       *              "spent_time": 00:00:00,
       *              "constraints": 0
       *            },
       *          "pending_task": {
       *              "task_category": "Dev Test",
       *              "name": "Task2",
       *              "spent_time": 00:00:00,
       *              "constraints": 0
       *           },
       *       }
       *  }
       */
    public function add()
    {
        if($this->is_database_conn != 'success') {
            $response = [
                'status' => 'Error',
                'message' => 'Validation, Unable to connect to '. $this->dbname .' Database.',
                '_serialize' => ['status', 'message']
            ];

            $this->set($response);
            return;
        }

        // validate/sanitize the requests params
        $errors = $this->validateRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

         $staff = $this->mystaffStaffsTable->getStaffByEmail($this->request->getData('username'));
        /**
         * get date period
         *
         * Note:
         * our application is saving information in UTC
         * in order to fetch data accurately, staff timezone on a requested date
         * must be considered.
         */
        $start = $this->getStartDate();
        $end = $this->getEndDate();

        $summary = $this->mystaffProductivitiesTable->computeSummary($staff, $start, $end);

        if ($summary['status'] == "Error") {
            $this->set([
                'status' => 'Error',
                'message' => $summary['message'],
                '_serialize' => ['status', 'message', 'summary']
            ]);
            return;
        }
        if ($this->summaryProductivitiesTable->save($summary)) {
            $data = [
                'username' => $staff->email,
                'client_name' => $summary['client_name'],
                'request_date' => date('Y-m-d', strtotime($end) ),
                'task' => $summary['task'],
                'pending' => $summary['pending'],
                'contraints' => 0,
                'accomplish_task' => empty($summary['accomplished_task']) ? NULL : json_decode($summary['accomplished_task']),
                'pending_task' => empty($summary['pending_task']) ? NULL : json_decode($summary['pending_task'])
            ];
          $this->set([
              'status' => 'Success',
              'message' => 'Summary Productivity Report',
              'summary' => $data,
              '_serialize' => ['status', 'message', 'summary']
          ]);
        } else {
          $this->set([
              'status' => 'Error',
              'message' => 'Internal Server Error',
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
        foreach($this->required_params as $param) {
            if (empty(trim($this->request->getData($param)))) {
                return [
                    'status' => 'Error',
                    'message' => 'Validation, '. $param .' is required.',
                    '_serialize' => ['status', 'message']
                ];
            }
        }

        $this->date = trim($this->request->getData('request_date'));

        /**
         * establish the staff entity
         */
        $this->staffEntity = $this->mystaffStaffsTable->establish($this->request->getData('username'));
        if (empty($this->staffEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate staff username. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
        }

        //get staff timezone on given date
        $staff_details = $this->ScShiftsTable->getShiftDetails($this->staffEntity->id, $this->date);

        if($staff_details) {
            $shift = explode(' to ',$staff_details['shift']);
            $this->shift_start = $shift[0];
            $this->shift_end = $shift[1];
            $this->timezone = new \DateTimeZone ($staff_details['current_timezone']);
        }
    }

    private function getStartDate()
    {
        if($this->staffEntity) {
            $startDate = new \DateTime($this->date .' '. $this->shift_start .':00' , $this->timezone);
        } else {
            $startDate = new \DateTime(trim($this->request->query('date')), $this->setDefaultTimezone);
            $startDate->setTime(0, 0, 0);
        }

        $startDate->setTimezone($this->setUTCTimezone);
        return $startDate->format($this->dateTimeFormat);
    }

    private function getEndDate()
    {
        if($this->staffEntity) {
            $endDate = new \DateTime($this->date . ' 23:59:59', $this->timezone);
        } else {
            $endDate = new \DateTime(trim($this->request->query('date')), $this->setDefaultTimezone);
            $endDate->setTime(23, 59, 59);
        }

        $endDate->setTimezone($this->setUTCTimezone);
        return $endDate->format($this->dateTimeFormat);
    }

}
