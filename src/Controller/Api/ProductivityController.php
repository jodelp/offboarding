<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

class ProductivityController extends AppController
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
    }

    /**
     * @api {post} /api/productivity.json Add new productivity
     * @apiName Add new productivity record
     * @apiGroup Productivity
     * @apiDescription Add a new productivity record. All parameters are **required** except for category.
     * Category field can be blank to cater saving of other record transaction such as breaks, meetings and change log such as (OTL and OUT)
     * @apiParam {String} username This is the staffs username
     * @apiParam {String} client The client name. This field is **required**
     * @apiParam {String} category Category name of the productivity task being added
     * @apiParam {String} type Type field. Can be any of the following *break, working, in and out*
     * @apiParam {String} description Record description details
     * @apiParam {String} status Productivity's status. Can be any of the following *pending, in progress, completed*
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {Number} id This is the productivity record id
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "id": 524125,
     *      "message": "New productivity was successfully saved.",
     *  }
     * @apiError {String} status Error status
     * @apiError {String} message error message
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 200 OK
     *          {
     *              "status": "Error",
     *              "message" : "Something went wrong. Data was not saved."
     *          }
     */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

        $table = TableRegistry::getTableLocator()->get('Productivities');

        $taskCategoryEntityId = empty($this->taskCategoryEntity) ? 0 : $this->taskCategoryEntity->id;

        $entity = $table->newEntity([
            'staff_id' => $this->staffEntity->id,
            'client_id' => $this->clientEntity->id,
            'task_category_id' => $taskCategoryEntityId,
            'type' => $this->request->getData('type'),
            'description' => $this->request->getData('description'),
            'status' => $this->request->getData('status'),
        ]);

        if ($table->save($entity)) {
            $this->set([
                'status' => 'Success',
                'message' => 'New productivity was successfully saved.',
                'id' => $entity->id,
                '_serialize' => ['status', 'id', 'message']
            ]);
        } else {
            $this->log('Error occurred when trying to save new productivity record.');
            $this->log($this->request->getData());
            $this->log($entity->getErrors());
            $this->set([
                'status' => 'Error',
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

        if (empty(trim($this->request->getData('client')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client name cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('type')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Type field cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('description')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Description cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('status')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Status field cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

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

        /**
         * establish the client entity
         */
        $this->clientEntity = $this->clientsTable->establish($this->request->getData('client'));
        if (empty($this->clientEntity)) {
            $this->set([
                'status' => 'Internal Error Occured',
                'message' => 'Unable to save record. Error encountered while trying to validate client name. Please report this incident to Admin.',
                '_serialize' => ['status', 'message']
            ]);
            return;
        }

        /**
         * establish the task category entity
         *
         * as per OwenP category can be blank, empty or zero(0)
         *
         */
        if (empty(trim($this->request->getData('category')))) {
            $this->taskCategoryEntity = null;
        } else {
            $this->taskCategoryEntity = $this->taskCategoriesTable->establish($this->request->getData('category'), $this->clientEntity);
            if (empty($this->taskCategoryEntity)) {
                $this->set([
                    'status' => 'Internal Error Occured',
                    'message' => 'Unable to save record. Error encountered while trying to validate category. Please report this incident to Admin.',
                    '_serialize' => ['status', 'message']
                ]);
                return;
            }
        }

        return [];
    }
}
