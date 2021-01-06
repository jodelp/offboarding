<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/AddSentEmailStatus Controller
 *
 *
 * @method \App\Model\Entity\Api/AddSentEmailStatus[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AddSentEmailStatusController extends AppController
{
    /**
     * Clients Table
     * @var App\Model\Table\ClientsTable
     */
    private $SentEmailsTable;

    /**
     * Clients Table
     * @var App\Model\Table\ClientsTable
     */
    private $StaffsTable;

    /**
     *
     * init method
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->SendEmailsTable = TableRegistry::getTableLocator()->get('SentEmails');
        $this->StaffsTable = TableRegistry::getTableLocator()->get('Staffs');

    }

    /**
     * @api {post} /api/add_sent_email_status.json Status of the sent email
     * @apiName Sent Email Status
     * @apiDescription Logs the status and details of the sent email.
     * @apiGroup Email
     * @apiParam {String} description Decription of the email being sent. Ex. DAR (optional)
     * @apiParam {String} username Username of the staff (required)
     * @apiParam {String} client_code Short code of the current client of the staff (required)
     * @apiParam {String} email_provider Name of the email provider (required)
     * @apiParam {String} email_recipient Email address of the recipient. If multiple, separate them with comma (required)
     * @apiParam {String} status Status of the email either sent or failed (required)
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Successfully saved sent email status"
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

        $data = [
            'description'       => $this->request->getData('description') != null ? $this->request->getData('description') : '',
            'username'          => $this->request->getData('username'),
            'client_code'       => $this->request->getData('client_code'),
            'email_provider'    => $this->request->getData('email_provider'),
            'email_recipient'   => $this->request->getData('email_recipient'),
            'status'            => $this->request->getData('status'),
        ];

        $sent_email_entity = $this->SendEmailsTable->newEntity($data);

        if($this->SendEmailsTable->save($sent_email_entity)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Successfully saved sent email status',
                '_serialize' => ['status', 'message']
            ]);

        } else {
            $this->set([
                'status' => 'Failed',
                'message' => 'Something went wrong. Data was not saved.',
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
        if (empty(trim($this->request->getData('username')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Staff username cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('email_provider')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Email provider cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('email_recipient')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Email recipient cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('status')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, status cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        /**
         * establish the staff entity
         */
        $this->staffEntity = $this->StaffsTable->establish($this->request->getData('username'));
        if (empty($this->staffEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate staff username. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
        }

    }

}
