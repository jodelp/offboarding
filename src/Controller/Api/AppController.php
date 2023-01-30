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

    const TIMEZONE_CODE_AU_ACT = 'AU-ACT';
    const TIMEZONE_CODE_AN_NSW = 'AU-NSW';
    const TIMEZONE_CODE_AU_NT = 'AU-NT';
    const TIMEZONE_CODE_AU_QLD = 'AU-QLD';
    const TIMEZONE_CODE_AU_SA = 'AU-SA';
    const TIMEZONE_CODE_AU_TAS = 'AU-TAS';
    const TIMEZONE_CODE_AU_VIC = 'AU-VIC';
    const TIMEZONE_CODE_AU_WA = 'AU-WA';
    const TIMEZONE_CODE_CA = 'CA';
    const TIMEZONE_CODE_CN = 'CN';
    const TIMEZONE_CODE_DE = 'DE';
    const TIMEZONE_CODE_HK = 'HK';
    const TIMEZONE_CODE_NZ = 'NZ';
    const TIMEZONE_CODE_PH = 'PH';
    const TIMEZONE_CODE_SG = 'SG';
    const TIMEZONE_CODE_UK = 'UK';
    const TIMEZONE_CODE_US = 'US';
    const TIMEZONE_CODE_US_NJ = 'US-NJ';

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

    public function validateDate($date)
    {
        $date_data = explode('-', $date);

        if(count($date_data) < 3) {
            return false;
        }

        if(strlen($date_data[0]) != 4) {
            return false;
        }

        return checkdate($date_data[1], $date_data[2], $date_data[0]);
    }

    /**
     * Return the timezone
     * @param string $tzCode
     * @return string
     */
    public function setTimezone($tzCode): string
    {
        $tz = '';

        switch (strtoupper($tzCode)) {
            case self::TIMEZONE_CODE_UK:
                $tz = 'Europe/London';
                break;
            case self::TIMEZONE_CODE_US:
                $tz = 'America/New_York';
                break;
            case self::TIMEZONE_CODE_PH:
            default:
                $tz = 'Asia/Manila';
                break;
        }

        switch (strtoupper($tzCode)) {
            case self::TIMEZONE_CODE_AU_ACT;
                $tz = 'Australia/Lindeman';
                break;
            case self::TIMEZONE_CODE_AN_NSW;
                $tz = 'Australia/Sydney';
                break;
            case self::TIMEZONE_CODE_AU_NT;
                $tz = 'Australia/Darwin';
                break;
            case self::TIMEZONE_CODE_AU_QLD;
                $tz = 'Australia/Brisbane';
                break;
            case self::TIMEZONE_CODE_AU_SA;
                $tz = 'Australia/Adelaide';
                break;
            case self::TIMEZONE_CODE_AU_TAS;
                $tz = 'Australia/Currie ';
                break;
            case self::TIMEZONE_CODE_AU_VIC;
                $tz = 'Australia/Melbourne';
                break;
            case self::TIMEZONE_CODE_AU_WA;
                $tz = 'Australia/Perth';
                break;
            case self::TIMEZONE_CODE_CA;
                $tz = 'America/Toronto';
                break;
            case self::TIMEZONE_CODE_CN;
                $tz = 'Asia/Hong_Kong';
                break;
            case self::TIMEZONE_CODE_DE;
                $tz = 'Europe/Berlin';
                break;
            case self::TIMEZONE_CODE_HK;
                $tz = 'Asia/Hong_Kong';
                break;
            case self::TIMEZONE_CODE_NZ;
                $tz = 'Pacific/Tarawa';
                break;
            case self::TIMEZONE_CODE_PH;
                $tz = 'Asia/Manila';
                break;
            case self::TIMEZONE_CODE_SG;
                $tz = 'Asia/Singapore';
                break;
            case self::TIMEZONE_CODE_UK;
                $tz = 'Europe/London';
                break;
            case self::TIMEZONE_CODE_US;
                $tz = 'America/New_York';
                break;
            case self::TIMEZONE_CODE_US_NJ;
                $tz = 'America/New_York';
                break;
            default:
                $tz = 'Asia/Manila';
                break;
        }

        return $tz;
    }
}
