<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use App\Service\Cms;

/**
 * Api/TaskCategoriesAdd Controller
 *
 *
 * @method \App\Model\Entity\Api/TaskCategoriesAdd[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TaskCategoriesAddController extends AppController
{
    /**
     * Cms service class
     * @var App\Service\Cms
     */
    private $CmsService;

    /**
     * Client entity
     * @var App\Model\Entity\Client
     */
    private $clientEntity;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');

        $this->CmsService = new Cms();
        $this->clientEntity = null;

        $this->loadModel('Clients');
        $this->loadModel('TaskCategories');
    }

    /**
     * @api {post} /api/task_categories/add.json Add New Task Category
     * @apiName Add New Task Category
     * @apiDescription Add new task category for clients
     *
     * @apiGroup Task Categories
     * @apiParam {String} client_code This is the client short code. **Required Field**
     * @apiParam {String} task_category_name This is the task category name. **Required Field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} client_code Client Short Code
     * @apiSuccess {String} name Client Name
     * @apiSuccess {Integer} id Task Category ID
     * @apiSuccess {String} task_category_name Task Category name
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Successfully added a new task category.",
     *      "client_code": "CLOUDSTAFF",
     *      "name": "Cloudstaff PH Inc",
     *      "id" : 1,
     *      "task_category_name": "Sample Category"
     *  }
     */
    public function add()
    {
        // validate/sanitize the requests params
        $errors = $this->validateEditRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }

        $entity = $this->taskCategoriesTable->newEntity([
          'client_id' => $this->clientEntity->id,
          'name' => $this->request->getData('task_category_name')
        ]);

        if ($this->taskCategoriesTable->save($entity)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Successfully added a new task category',
                'client_code' => $this->clientEntity->short_code,
                'name' => $this->clientEntity->name,
                'id' => $this->taskCategoriesTable->save($entity)->id,
                'task_category_name' => $this->taskCategoriesTable->save($entity)->name,
                '_serialize' => ['status', 'message', 'client_code', 'name', 'id', 'task_category_name']
            ]);
        } else {
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong. Data was not saved.',
                '_serialize' => ['status', 'message']
            ]);
        }
        return;

    }

    /**
     * Perform sanity checking and validation on required params
     * @return array
     */
    private function validateEditRequests()
    {
        if (empty(trim($this->request->getData('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        if (empty(trim($this->request->getData('task_category_name')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Task Category Name cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }


        $client = $this->CmsService->getDetails($this->request->getData('client_code'));
        if (empty($client)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Invalid client code',
                '_serialize' => ['status', 'message']
            ];
        }

        // establish client
        $this->clientEntity = $this->Clients->establish($client['name'], $this->request->getData('client_code'));

        $this->taskCategories = $this->taskCategoriesTable->find()->where(['client_id' => $this->clientEntity->id, 'name' => $this->request->getData('task_category_name')])->first();
        if ($this->taskCategories) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Task Categories already exist',
                '_serialize' => ['status', 'message']
            ];
        }
    }
}
