<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

class FormsController extends AppController
{

    /**
     * Displays a view
     *
     * @param array ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function index($employee_id)
    {
        $this->layout = 'offboarding';
        $this->loadModel('Departments');
        $this->loadModel('SubmittedForms');
        $forms = $this->Departments->find('all', [
            'contain' => [
                'Forms' => ['SubForms' => ['SubmittedForms'], 'SubmittedForms']
            ],
        ])->toArray();
        $this->set(compact('forms'));

        if ($this->request->is('post')) {
            //delete existing data then save new data
            $this->SubmittedForms->deleteAll(['user_id' => $employee_id]);
            if(count($this->request->getData()['form'])) {
                foreach($this->request->getData()['form'] as $key => $value) {
                    if(isset($value['id'])) {
                        $form = $this->SubmittedForms->newEntity();
                        $form->user_id = $employee_id;
                        $form->form_id = $value['id'];
                        $form->remarks = $value['remarks'];
                        $form->assessed_by = 1;//$this->Auth->user('id');
                        $this->SubmittedForms->save($form);
                    }
                }
            }
            if(count($this->request->getData()['subform'])) {
                foreach($this->request->getData()['subform'] as $key => $value) {
                    if(isset($value['id'])) {
                        $form = $this->SubmittedForms->newEntity();
                        $form->user_id = $employee_id;
                        // $form->form_id = $value['parent_id'];
                        $form->subform_id = $value['id'];
                        $form->remarks = $value['remarks'];
                        $form->assessed_by = 1;//$this->Auth->user('id');
                        $this->SubmittedForms->save($form);
                    }
                }
            }
            $this->Flash->set('The form has been saved.', ['element' => 'success']);
            return $this->redirect($this->referer());
        }
    }

    public function list()
    {

    }

}
