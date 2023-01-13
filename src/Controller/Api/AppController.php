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
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Firebase\JWT\JWT;
use Cake\Core\Configure;
use Exception;
use Cake\Datasource\ConnectionManager;

/**
 * Api Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    const CONNECTION_NAMES = ['cs_cib' => 'Staff Central', 'mystaff' => 'Mystaff'];

    /**
     * Token
     * @var string
     */
    private $token;


    /**
     * Application salt
     * @var string
     */
    private $applicationSalt;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');


        /**
         * load the application salt
         */
        $this->applicationSalt = Configure::read('App.applicationSalt');
        if (empty($this->applicationSalt)) {
            throw new Exception('Configuyration error. Application salt is not yet configured. Please check this again.');
        }

        /**
         * Load the Auth component using JWT
         *
         */
        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'queryDatasource' => false,
                    'key' => $this->applicationSalt,
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => false
        ]);

        /**
         * let us process the token if its available
         */
        if ($this->request->getEnv('HTTP_AUTHORIZATION')) {
            $token = substr($this->request->getEnv('HTTP_AUTHORIZATION'),7);
            $this->token = JWT::decode($token, $this->applicationSalt, array('HS256'));
            \Cake\Cache\Cache::write('namespace', $this->token->namespace);
        }

        $this->RequestHandler->ext = 'json';
    }


    /**
     * Get the application id
     * @return string
     */
    protected function getAppId()
    {
        return $this->token->appid;
    }


    /**
     * Get the namespace
     * @return string
     */
    protected function getRealm()
    {
        return $this->token->namespace;
    }

    public function isDbUp ($db_conn = [])
    {
        $status = [];
        foreach($db_conn as $db_name) {
            try{
                $this->db_mystaff_conn = ConnectionManager::get($db_name);
                $this->db_mystaff_conn->disconnect();
                $this->db_mystaff_conn->connect();
            }catch(\Exception $e){
                $this->log($e->getMessage());

                return $status['fail'] = self::CONNECTION_NAMES[$db_name];
            }
        }

        return $status['success'] = 'success';
    }
}
