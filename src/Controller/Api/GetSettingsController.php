<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;
use App\Service\Cms;


class GetSettingsController extends AppController
{
    const PARAM_TYPE_ARRAY = 'array';
    const MESSAGE_MATCH_FOUND = 'Match found';
    const MESSAGE_NO_ENGAGEMENT = 'No engagement details';
    const MESSAGE_RECORDS_FOUND = 'Records found';

    const STATUS_ENGAGE = 'engage';
    const STATUS_SUCCESS = 'Success';

    /**
     * WorkbenchSettings Table
     * @var App\Model\Table\WorkbenchSettingsTable
     */
    private $WorkbenchSettingsTable;

    /**
     * Staffs Table
     * @var App\Model\Table\StaffsTable
     */
    private $StaffsTable;

    /**
     * Cms Service class
     * @var App\Service\]Cms
     */
    private $cmsService;

    /**
     * Configurations Table
     * @var App\Model\Table\ConfigurationsTable
     */
    private $ConfigurationsTable;

    /**
     * Staff usernames
     * @var Cake\Collection\Collection
     */
    private $usernames;

    /**
     * Client entity
     * @var App\Model\Entity\Client
     */
    private $clientEntity;

    /**
     * Clients Table
     * @var App\Model\Table\ClientsTable
     */
    private $ClientsTable;

    /**
     *
     * init method
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->cmsService = new Cms();

        $this->ClientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');
        $this->WorkbenchSettingsTable = TableRegistry::getTableLocator()->get('WorkbenchSettings');

        $this->clientEntity = null;
        $this->usernames = new Collection([]);
    }

    /**
     * @api {post} /api/api/settings/get.json Get interval settings
     * @apiName Interval Settings
     * @apiGroup Workbench Settings
     * @apiDescription Retrieve the interval settings for screen capture and idle time of client and staff
     *
     * Consumed By
     * * Workbench App
     *
     * @apiParam {String} client_code Client short code
     * @apiParam {Array} [usernames] Staff username. Can accommodate multiple staffs request
     *
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} name Client name
     * @apiSuccess {String} client_code The given client short code
     * @apiSuccess {String} screenshot_interval Screenshot interval value express in seconds
     * @apiSuccess {String} idle_time Idle time setting value express in seconds
     * @apiSuccess {Array} staffs Will be available id there are staff requests
     * @apiSuccess {String} staffs.username Staff username
     * @apiSuccess {String} staffs.message Filter message
     * @apiSuccess {String} staffs.screenshot_interval Staff screenshot interval value express in seconds
     * @apiSuccess {String} staffs.idle_time Staff idle time setting value express in seconds
     *
     * @apiSuccessExample {json} Success-Response:
     *      {
     *          "status": "Success",
     *          "message": "Records found",
     *          "name": "cloudstaff philippines inc.",
     *          "client_code": "cloudstaff",
     *          "screenshot_interval": 380,
     *          "idle_time": 300,
     *          "staffs": [
     *              {
     *                  "username": "allana@cloudstaff.com",
     *                  "message": "Match found",
     *                  "screenshot_interval": 380,
     *                  "idle_time": 300
     *              }
     *          ]
     *      }
     *
     * @apiSuccessExample {json} Success-Response:
     *      {
     *          "status": "Success",
     *          "message": "Records found",
     *          "name": "cloudstaff philippines inc.",
     *          "client_code": "cloudstaff",
     *          "screenshot_interval": 380,
     *          "idle_time": 300,
     *          "staffs": [
     *              {
     *                  "username": "unknown@cloudstaff.com",
     *                  "message": "No engagement details"
     *              }
     *          ]
     *      }
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

        /**
         * prepare response payload
         */
        $responseData = [
            'status' => self::STATUS_SUCCESS,
            'message' => self::MESSAGE_RECORDS_FOUND,
            'name' => $this->clientEntity->name,
            'client_code' => $this->clientEntity->short_code,
            'screenshot_interval' => intval($this->clientEntity->clients_workbench_setting->screen_capture) / 60,
            'idle_time' => intval($this->clientEntity->clients_workbench_setting->idle_time_starts_after) / 60
        ];

        /**
         * check if there where staffs request too
         */
        if (!$this->usernames->isEmpty()) {
            $staffs = [];
            foreach ($this->usernames->toList() as $username) {
                $engagements = $this->cmsService->getStaffEngagement($username);

                /**
                 * username is non-existent
                 */
                if (empty($engagements)) {
                    array_push($staffs, [
                        'username' => $username,
                        'message' => self::MESSAGE_NO_ENGAGEMENT
                    ]);
                    continue;
                }

                /**
                 * validate staff if in our records
                 */
                $staffEntity = $this->StaffsTable->establish($username, false);
                if (empty($staffEntity)) {
                    $this->log('near Api/GetSettingsController line 128');
                    $this->log('While fetching staff information from StaffsTable.establish(). Staff does not exixst.');
                    $this->log('Staff username is '. $username);

                    array_push($staffs, [
                        'username' => $username,
                        'message' => self::MESSAGE_NO_ENGAGEMENT
                    ]);
                    continue;
                }

                /**
                 * filter out the staff engagement details here
                 */
                $currentEngagement = $engagements->match([
                    'client_shortcode' => $this->clientEntity->short_code,
                    'status' => self::STATUS_ENGAGE
                ]);

                if ($currentEngagement->isEmpty()) {
                    array_push($staffs, [
                        'username' => $username,
                        'message' => self::MESSAGE_NO_ENGAGEMENT
                    ]);
                } else {
                    $settings = $this->WorkbenchSettingsTable->getSettings($staffEntity, $this->clientEntity);
                    if ($settings) {
                        array_push($staffs, [
                            'username' => $username,
                            'message' => self::MESSAGE_MATCH_FOUND,
                            'screenshot_interval' => intval($settings['screen_capture']) / 60,
                            'idle_time' => intval($settings['idle_time_starts_after']) / 60,
                        ]);
                    } else {
                        array_push($staffs, [
                            'username' => $username,
                            'message' => self::MESSAGE_MATCH_FOUND,
                            'screenshot_interval' => intval($this->clientEntity->clients_workbench_setting->screen_capture) / 60,
                            'idle_time' => intval($this->clientEntity->clients_workbench_setting->idle_time_starts_after) / 60
                        ]);
                    }
                }
            }

            if (count($staffs)) {
                $responseData['staffs'] = $staffs;
            }
        }


        $responseData['_serialize'] = array_keys($responseData);
        $this->set($responseData);
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
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        $this->clientEntity = $this->ClientsTable->find()
            ->contain(['ClientsWorkbenchSettings'])
            ->where(['short_code' => $this->request->getData('client_code')])
            ->first();
        if (empty($this->clientEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Unknown client code',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty($this->clientEntity->clients_workbench_setting)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, The client has no workbench settings yet',
                '_serialize' => ['status', 'message']
            ];
        }

        $usernames = $this->request->getData('usernames');
        if (gettype($usernames) === self::PARAM_TYPE_ARRAY) {
            $this->usernames = (new Collection($usernames))->filter(function ($username) {
                if (!empty($username)) {
                    return $username;
                }
            });
        }
    }
}
