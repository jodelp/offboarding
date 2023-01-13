<?php

namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use Cake\Http\Client;
use App\Service\Token;


class SendActivityReportController extends AppController
{

    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const GEARMAN_SERVER = 'gearman-server';

    private $token;

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

        $this->http = new Client();
        $this->token = new Token();
        $this->getHttp = new Client(['headers' => ['Authorization' => 'Bearer ' . $this->token->getToken()]]);
        $this->staffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->summaryProductivitiesTable = TableRegistry::getTableLocator()->get('SummaryProductivities');
        $this->configurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->endpoint = $this->configurationsTable->getConfig(self::CMS_ENDPOINT_FIELD);
        $this->gearmanServer = $this->configurationsTable->getConfig(self::GEARMAN_SERVER);

        $this->mystaffWorkbenchSettingsTable = TableRegistry::getTableLocator()->get('MystaffWorkbenchSettings');
        $this->mystaffStaffsTable = TableRegistry::getTableLocator()->get('MystaffStaffs');
        $this->mystaffClientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $this->mystaffUsersTable = TableRegistry::getTableLocator()->get('MystaffUsers');
        $this->mystaffSubgroupStaffsTable = TableRegistry::getTableLocator()->get('MystaffSubgroupStaffs');
        $this->mystaffProductivitiesTable = TableRegistry::getTableLocator()->get('MystaffProductivities');

        $this->ScShiftsTable = TableRegistry::getTableLocator()->get('SCShifts');

        $this->dateTimeFormat = 'Y-m-d H:i:s';
        $this->setDefaultTimezone = new \DateTimeZone('Asia/Manila');
        $this->setUTCTimezone = new \DateTimeZone('UTC');
    }

    /**
     * @api {post} /api/send_activity_report.json Send activity report
     * @apiName Send an Activity Report
     * @apiDescription Send activity report of a staff to Customer
     *
     * **Consumed By:**
     *  * Workbench App (Java Application)
     * @apiGroup Productivity
     * @apiParam {String} client This is the client name
     * @apiParam {String} username This is the staffs username
     * @apiParam {String} request_date The date request. (optional)
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "The Daily Activity Report of staff has been successfully sent.",
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

        //get Requested Date, If none, use current date
        if ($this->request->getData('request_date')) {
            $requestStartDate = date('Y-m-d', strtotime($this->request->getData('request_date')));
        } else {
            $requestStartDate = date('Y-m-d');
        }

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

        //new computation
        $staff = $this->staff;

        //set return message for null staff details or null client_details
        if(!$staff) {
            $this->set([
                'status'     => 'failed',
                'message'    => 'Unable to find staff\'s details on MyStaff database.',
                '_serialize' => ['status', 'message']
            ]);
        }

        $summary = $this->mystaffProductivitiesTable->computeSummary($staff, $start, $end);

        $latest_productivities = $summary ? $summary->toArray() : [];

       if(!$latest_productivities) {
           $this->set([
               'status'     => 'fail',
               'message'    => 'Staff has no activity report to be sent.',
               '_serialize' => ['status', 'message']
           ]);
       }

        $username = explode('@', $this->request->getData('username'));

        $staff_first_name = $this->staffEntity->first_name == null ? ucwords($username[0]) : $this->staffEntity->first_name;
        $staff_last_name = $this->staffEntity->last_name == null ? '' : $this->staffEntity->last_name;
        $staff_username = $this->staffEntity->username == null ? '' : $this->staffEntity->username;

        $client_details = $this->mystaffClientEntity;

        if(!$client_details) {
            $this->set([
                'status'     => 'failed',
                'message'    => 'Unable to find client details on MyStaff database.',
                '_serialize' => ['status', 'message']
            ]);
        }

        //Check if staff is engaged on subgroup in MyStaff subgroup_staff table
        $subgroup_details = $this->mystaffSubgroupStaffsTable->getStaffSubgroup($staff->id);

        $client_id = null;
        if($subgroup_details) {
            //check if client is child of parent client
            $isParentClient = $this->mystaffClientsTable->isChildClient($client_details->client_id, $subgroup_details->client_id);

            if(!$isParentClient) {
                $client_id = $client_details->client_id;
            } else {
                $client_id = $subgroup_details->client_id;

                //Get client id from MyStaff clients table using client short_code
                $client_details = $this->mystaffClientsTable->getClientByClientID($subgroup_details->client_id, $client_details->client_id);
            }

        } else {
            $client_id = $client_details->client_id;
        }

        //Get Email Recipient via Workbench Settings in MyStaff Database using staff_id and client_id
        $email_recipient_ids = $this->mystaffWorkbenchSettingsTable->getDAREmailRecipient_UserID($staff->id, $client_id);

        if(!$email_recipient_ids || $email_recipient_ids->dar_recipients == 0) {
            $this->set([
                'status'     => 'failed',
                'message'    => 'No assigned customer to receive the staff\'s DAR.',
                '_serialize' => ['status', 'message']
            ]);
        }

        $recipients_ids = explode(',', $email_recipient_ids->dar_recipients);

        //Get details of DAR Email recipients via MyStaff users table
        $email_recipient_details = $this->mystaffUsersTable->getUsersDetails($recipients_ids);

        if($latest_productivities) {

            if(!$email_recipient_details || $email_recipient_details == null) {
                $this->set([
                    'status'     => 'failed',
                    'message'    => 'Details of DAR email recipient could not be found.',
                    '_serialize' => ['status', 'message']
                ]);

            } else {
                $param_data = [
                    //TO DO: get actual POC
                    'poc'    => $email_recipient_details,
                    'client' => [
                        'id'                => $client_details->id,
                        'client_name'       => $client_details->name,
                        'client_code'       => $client_details->short_code
                    ],
                    'staff' => [
                        'id'                => $this->staffEntity->id,
                        'first_name'        => $staff_first_name,
                        'last_name'         => $staff_last_name,
                        'username'          => $staff_username,
                    ],
                    'data' => [
                        'request_date'       => $requestStartDate,
                        'task_count'         => $latest_productivities['task'],
                        'pending_count'      => $latest_productivities['pending'],
                        'constraint_count'   => isset($latest_productivities['constraint']) ?
                            $latest_productivities['constraint'] : 0,
                        'pending_tasks'      => $latest_productivities['pending_task'],
                        'accomplished_tasks' => $latest_productivities['accomplished_task']
                    ]
                ];

                //send dar via gearman

                // Create our client object
                $client = new \GearmanClient();

                $client->addServer($this->gearmanServer);

                $success = @$client->ping('test connection');

                if (!$success) {

                    //Failed sending DAR
                    $this->set([
                        'status'     => 'failed',
                        'message'    => 'Failed sending the Daily Activity Report of staff.',
                        '_serialize' => ['status', 'message']
                    ]);
                    //send log to MyStaff via API

                } else {
                    $params = json_encode($param_data);

                    //Send reverse job
                    $result = $client->doBackground("activity", $params);

                    if ($result) {
                        //Success sending DAR
                        $this->set([
                            'status'     => 'success',
                            'message'    => 'The Daily Activity Report of staff has been successfully sent.',
                            '_serialize' => ['status', 'message']
                        ]);
                    }
                }
            }

       } else {
            $this->set([
                'status'     => 'fail',
                'message'    => 'Staff has no activity report to be sent.',
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
        if (empty(trim($this->request->getData('client')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client name cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('username')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Staff username cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        $this->date = trim($this->request->getData('request_date'));

        /**
         * establish the staff entity
         */
        $this->staffEntity = $this->staffsTable->establish($this->request->getData('username'));
        if (empty($this->staffEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate staff username. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
        }

        $this->staff = $this->mystaffStaffsTable->getStaffByEmail($this->request->getData('username'));

        //get staff timezone on given date
        $staff_details = $this->ScShiftsTable->getShiftDetails($this->staff->id, $this->date);

        if($staff_details) {
            $shift = explode(' to ',$staff_details['shift']);
            $this->shift_start = $shift[0];
            $this->shift_end = $shift[1];
            $this->timezone = new \DateTimeZone ($staff_details['current_timezone']);
        }

        $this->mystaffClientEntity = $this->mystaffClientsTable->getClientByName($this->request->getData('client'));

        if(!$this->mystaffClientEntity) {
            return [
                'status' => 'Error',
                'message' => 'Validation, unable to find client details from Mystaff',
                '_serialize' => ['status', 'message']
            ];
        }

        if(!$this->mystaffClientEntity->shorthand) {
            return [
                'status' => 'Error',
                'message' => 'Validation, client has no shortcode in Mystaff. Please coordinate to System Admin.',
                '_serialize' => ['status', 'message']
            ];
        }

        /**
         * establish the client entity
         */
        $this->clientEntity = $this->clientsTable->establish($this->request->getData('client'), $this->mystaffClientEntity->shorthand);

        $this->clientEntity = $this->clientsTable->establish($this->request->getData('client'));
        if (empty($this->clientEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate client name. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
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
