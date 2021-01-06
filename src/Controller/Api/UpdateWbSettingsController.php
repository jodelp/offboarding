<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;
use Cake\Http\Client;

class UpdateWbSettingsController extends AppController
{
    const PARAM_TYPE_ARRAY = 'array';
    const WEBSOCKET_URL_FIELD = 'wb-websocket-url';
    const WEBSOCKET_ENDPOINT = 'updateSettingsEvent';
    const RESPONSE_STATUS_SUCCESS = 'Success';
    const RESPONSE_STATUS_ERROR = ' Error';

    /**
     * Configurations Table
     * @var App\Model\Table\ConfigurationsTable
     */
    private $ConfigurationsTable;

    /**
     * Array input of usernames
     * @var array
     */
    private $usernames;


    /**
     * init method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->usernames = null;

        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');
    }

    /**
     * @api {post} /api/update_wb_settings.json Update workbench settings
     * @apiName Update workbench settings
     * @apiGroup Workbench Settings
     * @apiDescription Push an update notification to Workbench websocket for the following given staffs
     *
     * Consumed By
     * * Workbench App
     *
     * @apiParam {Array} usernames[] list of staff usernames
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  {
     *
     *      "status": "Success",
     *      "message": "Data has been successfully applied to websocket."
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

        /**
         * compute the websocket url
         */
        $url = $this->ConfigurationsTable->getConfig(self::WEBSOCKET_URL_FIELD).self::WEBSOCKET_ENDPOINT;
        $http = new Client();
        $data = json_encode(['usernames' => $this->usernames->toList()]);
        $response = $http->post($url, $data, ['type' => 'json']);
        $json = $response->getJson();

        if ($json['status'] === self::RESPONSE_STATUS_SUCCESS) {
            $this->set([
                'status' => 'Success',
                'message' => 'Data has been successfully applied to websocket.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->set([
                'status' => 'Success',
                'message' => $json['message'],
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
        $usernames = $this->request->getData('usernames');
        if (gettype($usernames) !== self::PARAM_TYPE_ARRAY) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Usernames cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (count($usernames) and empty($usernames[0])) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Usernames cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        /**
         * filter blank usernames
         */
        $this->usernames = (new Collection($usernames))->filter(function ($username) {
            if (!empty($username)) {
                return $username;
            }
        });
    }
}