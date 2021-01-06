<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
//use App\Model\Entity\Staff;
//use App\Model\Entity\Client;
//use App\Model\Entity\TaskCategory;
//use App\Model\Table\TaskCategoriesTable;
//use App\Model\Table\StaffsTable;
//use App\Model\Table\ClientsTable;

class GetProductivityController extends AppController
{
    /**
     * Task Categories table
     * @var App\Model\Table\TaskCategoriesTable
     */
    private $taskCategoriesTable;

    /**
     * Task Category entity
     * @var App\Model\Entity\TaskCategory
     */
    private $taskCategoryEntity;

    /**
     * Clients table
     * @var App\Model\Table\ClientsTable
     */
    private $clientsTable;

    /**
     * Client Entity
     * @var App\Model\Entity\Client
     */
    private $clientEntity;

    /**
     * Staffs Table
     * @var App\Model\Table\StaffsTable
     */
    private $staffsTable;

    /**
     * Staff Entity
     * @var App\Model\Entity\Staff
     */
    private $staffEntity;


    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $this->productivitiesTable = TableRegistry::getTableLocator()->get('Productivities');

        //set default timezone 16:00:00
        $this->setDefaultTimezone = new \DateTimeZone('GMT+4');

        $this->dateTimeFormat = 'Y-m-d H:i:s';

        $this->timeFormat  = '%H:%I:%S';
    }

     /**
     * @api {post} /api/get_productivity.json Request staff productivity
     * @apiName GetProductivities
     * @apiGroup Productivity
     * @apiDescription Show the current productivities status
     *
     * Return the total working hours
     *
     * Return the total idle
     *
     * Return the total meeting hours
     *
     * Return the total break hours
     *
     * Return the total stopped hours
     *
     * **Consumed By:**
     *  * Workbench App (Java Application)
     *
     * @apiParam {String} username Staff username
     *
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {Object[]} data Data response
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *  {
     *     "status": "Success",
     *     "message": "Staff activity report.",
     *     "data": {
     *         "working": {
     *             "hrs": "00",
     *             "min": "00"
     *         },
     *         "idle": {
     *             "hrs": "00",
     *             "min": "00"
     *         },
     *         "meeting": {
     *             "hrs": "00",
     *             "min": "00"
     *         },
     *         "break": {
     *             "hrs": "00",
     *             "min": "00"
     *         },
     *         "stopped": {
     *             "hrs": "00",
     *             "min": "00"
     *         }
     *     }
     * }
     *
     */
    public function add()
    {
        if (!$this->request->is('post') || !$this->request->getData('username')) {

            $this->set([
                'status' => 'Error',
                'message' => 'Invalid request.',
                '_serialize' => ['status', 'message']
            ]);

            return;
        }

        $staff = $this->staffsTable->getIdByUsername($this->request->getData('username'));

        $start = $this->getStartDate();

        $end = $this->getEndDate();

        //getting the current task
        $productivities = $this->productivitiesTable->find()
            ->where([
                'staff_id' => $staff['id'],
                "created BETWEEN '{$start}' AND '{$end}'"
            ])
            ->order(['id' => 'ASC']);

        //return if the productivities is empty
        if (empty($productivities->toArray())) {
            $this->set([
                'status' => 'Error',
                'message' => 'No productivities to view.',
                '_serialize' => ['status', 'message']
            ]);

            return;
        }

        //set varialble to get time interval from start and end date
        $interval = '';

        //set from variables to false (boolean)
        $isFromIdle = $isFromBreak = $isFromMeeting = $isFromStopped = false;

        //set main variables to null
        $working = $meetings = $breaks = $idle = $stoppeds = 0;

        //set manila time zone as default
        $asiaMananilaTimezone = new \DateTimeZone('Asia/Manila');

        /***
         * Manipulate the productivities
         */
        $tasks = [];

        foreach ( $productivities as $productivity ) {

            $dateFormat  = new \DateTime($productivity->created->format($this->dateTimeFormat));

            $dateFormat->setTimezone($asiaMananilaTimezone);

                $tasks[] = [
                    'type' => $productivity->type,
                    'created' => $dateFormat,
                    'description' => $productivity->description,
                ];
        }


        $startDate = new \DateTime(current($tasks)['created']->format($this->dateTimeFormat));

        unset($tasks[0]);

        foreach ( $tasks as $task ) {

            $endDate = new \DateTime($task['created']->format($this->dateTimeFormat));

            $interval = date_diff($startDate, $endDate);

            if ( !$isFromIdle && !$isFromBreak && !$isFromMeeting && !$isFromStopped ) {
                $working = (!$working) ? strtotime($interval->format($this->timeFormat)) : $working + strtotime($interval->format($this->timeFormat));
            }

            /**
             * add interval to the designated main variable type
             */
            //set the total idle
            if ( $isFromIdle ) {
                $idle = (!$idle) ? strtotime($interval->format($this->timeFormat)) : $idle + strtotime($interval->format($this->timeFormat));
            }
            //set the total breaks
            if ( $isFromBreak ) {
                $breaks = (!$breaks) ? strtotime($interval->format($this->timeFormat)) : $breaks + strtotime($interval->format($this->timeFormat));
            }
            //set the total meetings
            if ( $isFromMeeting ) {
                $meetings = (!$meetings) ? strtotime($interval->format($this->timeFormat)) : $meetings + strtotime($interval->format($this->timeFormat));
            }
            //set the total stoppeds
            if ( $isFromStopped ) {
                $stoppeds = (!$stoppeds) ? strtotime($interval->format($this->timeFormat)) : $stoppeds + strtotime($interval->format($this->timeFormat));
            }
            /*** end of conditions ***/

            //set the variable to true before adding on main variables exept if type = 'working'
            switch ( $task['type'] ) {
                case 'working':
                    $isFromIdle = $isFromBreak = $isFromMeeting = $isFromStopped = false;
                    break;
                case 'idle':
                    $isFromIdle = true;
                    break;
                case 'meeting':
                    $isFromMeeting = true;
                    break;
                case 'break':
                    $isFromBreak = true;
                    break;
                case 'stopped':
                    $isFromStopped = true;
                    break;
            }

            //set the endDate to starDate
            $startDate = $endDate;
        }

        //format the out data
        $data = [
            'working' => [
                'hrs' => date('H', $working),
                'min' => date('i', $working),
            ],
            'idle' => [
                'hrs' => date('H', $idle),
                'min' => date('i', $idle),
            ],
            'meeting' => [
                'hrs' => date('H', $meetings),
                'min' => date('i', $meetings),
            ],
            'break' => [
                'hrs' => date('H', $breaks),
                'min' => date('i', $breaks),
            ],
            'stopped' => [
                'hrs' => date('H', $stoppeds),
                'min' => date('i', $stoppeds),
            ]
        ];

        $this->set([
            'status' => 'Success',
            'message' => 'Staff activity report.',
            'data' => $data,
            '_serialize' => ['status', 'message', 'data']
        ]);


    }


    private function getStartDate()
    {
        $selectDate = date($this->dateTimeFormat, strtotime('today midnight'));

        //'GMT+4' is -1 day 16:00:00 from UTC
        $currentDate = new \DateTime($selectDate, $this->setDefaultTimezone);

        $offset = $currentDate->getOffset();

        $setTimeStamp = $currentDate->getTimestamp() - $offset;

        return date($this->dateTimeFormat, $setTimeStamp);
    }

    private function getEndDate()
    {
        //get occurrence date and time
        $getTimestamp = new \DateTime('now', $this->setDefaultTimezone);

        return $getTimestamp->format($this->dateTimeFormat);
    }

}
