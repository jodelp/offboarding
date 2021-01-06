<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;

/**
 * Api/TaskCategoriesList Controller
 *
 *
 * @method \App\Model\Entity\Api/TaskCategoriesList[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TaskCategoriesListController extends AppController
{
    const GENERAL_CLIENT_ID = 0;


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

    }

    /**
     * @api {get} /api/task_categories/list.json List all task Categories
     * @apiName List all task Categories
     * @apiDescription Fetch available task categories for certain clients.
     * In returning the available task categories also include the generic task categories denoted by client_id = 0
     * @apiGroup Task Categories
     * @apiParam {String} client_code This is the client short code. **Required Field**
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccess {String} client_code Client Short Code
     * @apiSuccess {String} name Client Name
     * @apiSuccess {Array} items Available Tasks Categories
     * @apiSuccessExample {json} Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *      "status": "Success",
     *      "message": "Successfully added a new task category.",
     *      "client_code": "CLOUDSTAFF",
     *      "name": "Cloudstaff Philippines Inc",
     *      "id" : 1,
     *      "items": [
     *          {
     *            "id": 1,
     *            "name": "general",
     *          },
     *          {
     *            "id": 3,
     *            "name": "Sample Category"
     *          }
     *
     *      ]
     *  }
     */
    public function index()
    {
        // validate/sanitize the requests params
        $errors = $this->validateEditRequests();
        if (!empty($errors)) {
            $this->set($errors);
            return;
        }
        $taskCategories = $this->taskCategoriesTable->find()->where(function ($exp) {
              $orConditions = $exp->or(['client_id' => self::GENERAL_CLIENT_ID])
                  ->eq('client_id', $this->clientEntity->id);
                  return $exp
                  ->add($orConditions);
          })->all();

        $results = [];
        foreach ($taskCategories as $taskCategory) {
          $data = [
            'id' => $taskCategory->id,
            'name' => $taskCategory->name
          ];
          array_push($results, $data);
        }
        $this->set([
            'status' => 'Success',
            'message' => 'Matches found',
            'client_code' => $this->clientEntity->short_code,
            'name' => $this->clientEntity->name,
            'items' => $results,
            '_serialize' => ['status', 'message', 'client_code', 'name', 'items']
        ]);
        return;
    }

    /**
     * Perform sanity checking and validation on required params
     * @return array
     */
    private function validateEditRequests()
    {

        if (empty(trim($this->request->getQuery('client_code')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Client code cannot be blank',
                '_serialize' => ['status', 'message']
            ];
        }

        $this->clientEntity = $this->clientsTable->find()->where(['short_code' => $this->request->getQuery('client_code')])->first();
        if (empty($this->clientEntity)) {
            return [
                'status' => 'Error',
                'message' => 'Validation, No Client Short Code cannot be found',
                '_serialize' => ['status', 'message']
            ];
        }

      }

}
