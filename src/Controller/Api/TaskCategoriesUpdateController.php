<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
/**
 * Api/TaskCategoriesUpdate Controller
 *
 *
 * @method \App\Model\Entity\Api/TaskCategoriesUpdate[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TaskCategoriesUpdateController extends AppController
{
    const GENERAL_CLIENT_ID = 0;

    /**
     * initialize method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $this->taskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');

    }

    /**
     * @api {post} /api/task_categories/update.json Update Existing Task Category
     * @apiName Update Existing Task Category
     * @apiDescription Update Existing Task Category for Clients
     *
     * @apiGroup Task Categories
     * @apiParam {String} task_category_id This is the Task Category ID. **Required Field**
     * @apiParam {String} task_category_name This is the task category name. **Required Field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} client_code Client Short Code
     * @apiSuccess {String} name Client Name
     * @apiSuccess {String} id Task Category ID
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Successfully updated a task category",
     *      "client_code": "CLOUDSTAFF",
     *      "name": "Cloudstaff Philippines Inc",
     *      "id" : 1,
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

          $taskCategory = $this->taskCategoriesTable->patchEntity($this->taskCategoryEntity, ['name' => $this->request->getData('task_category_name')]);

          if ($this->taskCategoriesTable->save($taskCategory)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Successfully updated a new task category',
                'client_code' => !empty($this->clientEntity) ? $this->clientEntity->short_code : null,
                'name' => !empty($this->clientEntity) ? $this->clientEntity->name : null,
                'id' => $this->taskCategoriesTable->save($taskCategory)->id,
                '_serialize' => ['status', 'message', 'client_code', 'name', 'id']
            ]);
          } else {
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
    private function validateEditRequests()
    {
        if (empty(trim($this->request->getData('task_category_id')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Task Category ID cannot be blank',
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

        $this->taskCategoryEntity = $this->taskCategoriesTable->find()->where(['id' => $this->request->getData('task_category_id')])->first();
        if (empty($this->taskCategoryEntity)) {
          return [
              'status' => 'Error',
              'message' => 'Validation, No Task Category Found',
              '_serialize' => ['status', 'message']
          ];
        }

        if ($this->taskCategoryEntity->client_id != self::GENERAL_CLIENT_ID) {
            $this->clientEntity = $this->clientsTable->find()->where(['id' => $this->taskCategoryEntity->client_id])->first();
        }

        $taskCateg = $this->taskCategoriesTable->find()->where(['name' => $this->request->getData('task_category_name'), 'id !=' => $this->request->getData('task_category_id'), 'client_id' => $this->taskCategoryEntity->client_id ])->first();
        if ($taskCateg) {
          return [
              'status' => 'Error',
              'message' => 'Validation, Task Category already Exists',
              '_serialize' => ['status', 'message']
          ];
        }

      }

}
