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

use Cake\Controller\Controller;
use Cake\Event\Event;
Use Cake\Network\Email\Email;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        // $this->loadComponent('Auth', [
        //     'loginAction'=> [
        //         'controller' => 'Users',
        //         'action'=>'login'
        //     ],
        //     'logoutRedirect'=>[
        //         'controller'=>'Users',
        //         'action'=>'login'
        //     ],
        //     'storage'=>'Session'
        // ]);
        
        $buildVersion = env('APP_BUILD', null);
        $this->set(compact('buildVersion'));
        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

    public function sendOtp($userEmail, $otp)
    {
        $email = new Email();
        $email->transport('gmail')
        ->from(['osh.committee@cloudstaff.com' => 'OffBoarding System'])
        ->to($userEmail)
        ->subject('Staff Offboarding OTP')
        ->emailFormat('html');

        $email->viewVars([
            'user_email' => $userEmail,
            'otp' => $otp
        ])->template('test');

        if ($email->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function offBoardingUpdate($userEmail, $link)
    {
        $email = new Email();
        $email->transport('gmail')
        ->from(['osh.committee@cloudstaff.com' => 'OffBoarding System'])
        ->to($userEmail)
        ->subject('Staff Offboarding')
        ->emailFormat('html');

        $email->viewVars([
            'user_email' => $userEmail,
            'link' => $link
        ])->template('update');

        if ($email->send()) {
            return true;
        } else {
            return false;
        }
    }
}
