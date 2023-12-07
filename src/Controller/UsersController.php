<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Users Controller
 *
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set('user', $user);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function signup()
    {
        $this->viewBuilder()->autoLayout(false);
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            //check if user exists
            $user->username = $this->request->getData('username');
            $user->employee_id = $this->request->getData('employee_id');
            $user->first_name = 'test';
            $user->last_name = 'test';
            $user->client_name = 'test';
            $user->role = 'staff';
            $user->otp = $this->generateOtp();

            if ($this->Users->save($user)) {
                $this->sendOtp($user->username, $user->otp);
                $this->Flash->success(__('We\'ve sent you an email with your OTP code .'));
                return $this->redirect(['action' => 'signup']);
            }
            $this->Flash->error(__('Unable to send OTP Code. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    public function login()
    {
        $this->viewBuilder()->autoLayout(false);
        if ($this->request->is('post')) {
            $username = $this->request->getData('username');
            $otp = $this->request->getData('otp');
            $user = $this->Users->find('all', [
                'conditions' => [
                    'username' => $username,
                    'otp' => $otp
                ]
            ])->first();

            if ($user) {
                // $this->Auth->setUser($user);
                if ($user->role == 'admin') {
                    return $this->redirect(['controller' => 'forms', 'action' => 'index']);
                } else  if ($user->role == 'poc') {
                    return $this->redirect(['controller' => 'forms', 'action' => 'index']);
                } else if ($user->role == 'staff') {
                    return $this->redirect(['controller' => 'forms', 'action' => 'index', $user->employee_id]);
                } else {
                    $this->Flash->error('Login Failed');
                }
            }
            $this->Flash->error('Login Failed');
        }

        return $this -> render('/Users/signup');
    }

    public function generateOtp()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $length = 6;
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
    
        return $randomString; 
    }

    public function logout()
    {
        $this->Flash->success('You are now logged out.');
        return $this->redirect(['controller' => 'users', 'action' => 'signup']);
        // return redirect ($this->Auth->logout());
        // return $this->redirect($this->Auth->logout());
    }
}
