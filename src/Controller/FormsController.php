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
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class FormsController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        // allow only login, forgotpassword
         $this->Auth->allow(['index', 'list', 'completed']);
    }

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
        $this->loadModel('Users');
        //resignees
        // pr($this->Auth->user('role'));exit;
        $staffInfo = $this->Users->get($employee_id)->toArray();
        //currentyl logged in
        $user = $this->Users->get($this->Auth->user('id'), ['contain' => 'Pocs'])->toArray();
        if($this->Auth->user('role') == 'staff' || $this->Auth->user('role') == 'admin') {
            $forms = $this->Departments->find('all', [
                'contain' => [
                    'Forms' => [
                        'SubForms' => [
                            // 'SubmittedForms' => ['Pocs'],
                            'SubmittedForms' => function ($q) use($employee_id) {
                                $q->contain('Pocs');
                                return $q->where(["SubmittedForms.user_id" => $employee_id]);
                            }
                        ],
                        // 'SubmittedForms' => ['Pocs'],
                        'SubmittedForms' => function ($q) use($employee_id) {
                            $q->contain('Pocs');
                            return $q->where(["SubmittedForms.user_id" => $employee_id]);
                        }
                    ]
                ],
            ])->toArray();
            $this->set(compact('forms','staffInfo'));
            $this->render('myclearance');
        } else {
            $forms = $this->Departments->find('all', [
                'contain' => [
                    'Forms' => [
                        'SubForms' => [
                            // 'SubmittedForms' => ['Pocs'],
                            'SubmittedForms' => function ($q) use($employee_id) {
                                $q->contain('Pocs');
                                return $q->where(["SubmittedForms.user_id" => $employee_id]);
                            }
                        ],
                        // 'SubmittedForms' => ['Pocs'],
                        'SubmittedForms' => function ($q) use($employee_id) {
                            $q->contain('Pocs');
                            return $q->where(["SubmittedForms.user_id" => $employee_id]);
                        }
                    ]
                ],
                'conditions' => [
                    'Departments.id' => $user['poc']['department_id'],
                    // 'SubmittedForms.user_id' => $employee_id
                ],
            ])->toArray();
        }
        
        $this->set(compact('forms','staffInfo'));

        if ($this->request->is('post')) {
            //delete existing data then save new data
            $this->SubmittedForms->deleteAll([
                'user_id' => $employee_id,
                'poc_id' => $user['poc']['id']
            ]);
            if($this->request->getData('form')) {
                foreach($this->request->getData('form') as $key => $value) {
                    if(isset($value['id'])) {
                        $form = $this->SubmittedForms->newEntity();
                        $form->user_id = $employee_id;
                        $form->form_id = $value['id'];
                        $form->remarks = $value['remarks'];
                        $form->poc_id = $user['poc']['id'];//$this->Auth->user('id');
                        $this->SubmittedForms->save($form);
                    }
                }
            }
            if($this->request->getData('subform')) {
                foreach($this->request->getData('subform') as $key => $value) {
                    if(isset($value['id'])) {
                        $form = $this->SubmittedForms->newEntity();
                        $form->user_id = $employee_id;
                        // $form->form_id = $value['parent_id'];
                        $form->subform_id = $value['id'];
                        $form->remarks = $value['remarks'];
                        $form->poc_id = $user['poc']['id'];//$this->Auth->user('id');
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
        $this->layout = 'offboarding';
        $this->loadModel('Users');
        $users = $this->Users->find('all', [
            'conditions' => [
                'Users.role' => 'staff'
            ]
        ])->toArray();
        $this->set(compact('users'));
    }

    public function completed($employee_id)
    {
        $this->autoRender = false;
        $this->autoLayout = false;
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $form = $userTable->get($employee_id);
        $form->status = 'done';
        if($userTable->save($form)) {
            $this->Flash->set('Staff clearance has been successfully completed.', ['element' => 'success']);
            return $this->redirect('/forms/list');
        } else {
            $this->Flash->set('Unable to update staff clearance.', ['element' => 'error']);
            return $this->redirect($this->referer());
        }
    }

}
