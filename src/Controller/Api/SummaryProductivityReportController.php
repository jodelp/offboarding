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

          $this->mystaffProductivitiesTable = TableRegistry::getTableLocator()->get('MystaffProductivities');
          $this->mystaffStaffsTable = TableRegistry::getTableLocator()->get('MystaffStaffs');
          $this->summaryProductivitiesTable = TableRegistry::getTableLocator()->get('SummaryProductivities');

          $this->dateTimeFormat = 'Y-m-d H:i:s';
          $this->setDefaultTimezone = new \DateTimeZone('Asia/Manila');
          $this->setUTCTimezone = new \DateTimeZone('UTC');
      }

      /**
       * @api {post} /api/summary_productivity_report.json Summary productivity of staff
       * @apiName Summary Productivity Report
       * @apiDescription Return the summary productivity of the staff on the indicated request date.
       * @apiGroup Productivity
       * @apiParam {String} username This is the staffs username
       * @apiParam {String} [request_date] The date request. This date is expected as _**Asia/Manila**_ timezone
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

        // validate/sanitize the requests params
        $errors = $this->validateRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

         $staff = $this->mystaffStaffsTable->getStaffByEmail($this->request->getData('username'));
        /**
         * get StartDate
         *
         * Note:
         * our application is saving information in UTC in order to fetch a PHT date
         * one must use date coverage for handling the request date, i.e 2020-03-10 PHT is equivalent to
         * From > 2020-03-09 16:00:00 To > 2020-03-10 15:59:59 in UTC
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
        if (empty(trim($this->request->getData('username')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Staff username cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

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
    }

    private function getStartDate()
    {
        if (!$this->request->getData('request_date')) {
          $startDate = new \DateTime('now' ,$this->setDefaultTimezone);
        } else {
          $startDate = new \DateTime( $this->request->getData('request_date') ,$this->setDefaultTimezone);
        }
        $startDate->setTime(0, 0, 0);
        $startDate->setTimezone($this->setUTCTimezone);
        return $startDate->format($this->dateTimeFormat);
    }

    private function getEndDate()
    {
        if (!$this->request->getData('request_date')) {
          $endDate = new \DateTime('now', $this->setDefaultTimezone);
        } else {
          $endDate = new \DateTime($this->request->getData('request_date'), $this->setDefaultTimezone);
        }
        $endDate->setTime(23, 59, 59);
        $endDate->setTimezone($this->setUTCTimezone);
        return $endDate->format($this->dateTimeFormat);
    }

}
