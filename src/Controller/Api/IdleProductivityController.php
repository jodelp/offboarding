<?php
namespace App\Controller\Api;

use Cake\ORM\TableRegistry;
use Cake\Datasource\Exception\RecordNotFoundException;

class IdleProductivityController extends AppController
{
    /**
     * Productivity entity
     * @var App\Model\Entity\Productivity
     */
    private $entity;

    /**
     * Productivities Table
     * @var App\Model\Table\ProductivitiesTable
     */
    private $ProductivitiesTable;

    /**
     *
     * init method
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->ProductivitiesTable = TableRegistry::getTableLocator()->get('Productivities');
        $this->entity = null;
    }

    /**
     * @api {post} /api/productivities/idle_update.json Idle update
     * @apiName Idle update
     * @apiGroup Productivity
     * @apiDescription Updates the idle description of a productivity record
     * 
     * Consumed By
     * * Workbench App
     *
     * @apiParam {Integer} id Productivity record id
     * @apiParam {String} description The updated productivity description
     * @apiSuccess {String} status Status label of your request, either success or error
     * @apiSuccess {String} message Status message
     * @apiSuccessExample {json} Success-Response:
     *  {
     *
     *      "status": "Success",
     *      "message": "Record was successfully updated."
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

        $this->entity->description = trim($this->request->getData('description'));
        if ($this->ProductivitiesTable->save($this->entity)) {
            $this->set([
                'status' => 'Success',
                'message' => 'Record was successfully updated.',
                '_serialize' => ['status', 'message']
            ]);
        } else {
            $this->log($this->entity->getErrors());
            $this->set([
                'status' => 'Error',
                'message' => 'Something went wrong, record was not updated. Please report this to admin',
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
        if (empty(trim($this->request->getData('id')))) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Productivity Id cannot be blank',
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

        try {
            $this->entity = $this->ProductivitiesTable->get($this->request->getData('id'));
        } catch (RecordNotFoundException $ex) {
            return [
                'status' => 'Error',
                'message' => 'Validation, Record not found',
                '_serialize' => ['status', 'message']
            ];
        }
    }
}