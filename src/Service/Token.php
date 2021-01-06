<?php
namespace App\Service;

use Cake\ORM\TableRegistry;
use Cake\Http\Client;
use Cake\Core\Exception\Exception;
use Cake\Cache\Cache;
use Cake\Log\Log;

class Token
{
    const FIELD_URL = 'identityserv-url';
    const FIELD_APP_ID = 'identityserv-app-id';
    const FIELD_APP_KEY = 'identityserv-app-key';

    const TOKEN_ENDPOINT = 'token.json';
    const HEALTHCHECK_ENPOINT = 'healthcheck.json';

    const CACHE_KEY = 'token';
    const CACHE_CONFIG_SHORT = 'cache_short';

    /**
     * Configurations table
     * @var Cake\ORM\Table
     */
    private $ConfigurationsTable;

    /**
     * Endpoint
     * @var string
     */
    private $endpoint;

    /**
     * App id
     * @var string
     */
    private $appId;

    /**
     * App key
     * @var string
     */
    private $appKey;

    /**
     * __construct method
     * @return void
     */
    public function __construct()
    {
        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');

        $this->endpoint = $this->ConfigurationsTable->getConfig(self::FIELD_URL);
        $this->appId = $this->ConfigurationsTable->getConfig(self::FIELD_APP_ID);
        $this->appKey = $this->ConfigurationsTable->getConfig(self::FIELD_APP_KEY);
    }

    /**
     * return a token from users microservice
     * @return string
     * @throws Exception
     */
    public function getToken()
    {
        if (($token = Cache::read(self::CACHE_KEY, self::CACHE_CONFIG_SHORT)) === false) {
            $token = $this->doRequestToken();
        }

        $token = $this->validateToken($token);
        return $token;
    }

    /**
     * Request a new token to Identity Serv
     * @return string
     * @throws Exception
     */
    private function doRequestToken()
    {
        $http = new Client();
        $url = $this->endpoint . self::TOKEN_ENDPOINT;

        $response = $http->post($url, [
            'app_id' => $this->appId,
            'app_key' => $this->appKey
        ]);

        $result = $response->getJson();
        if (isset($result['success'])) {
            $token = $result['token'];
            Cache::write(self::CACHE_KEY, $token, self::CACHE_CONFIG_SHORT);
        } else {
            Log::error($result['message']);
            Log::error('At Token component near getToken()');
            Log::error('App Id: [' . $this->appId . '] App Key: [' . $this->appKey . ']');
            throw new Exception($result['message'] . '. Please see the logs for details.');
        }

        return $token;
    }

    /**
     * Let us validate our existing token to see if its still valid
     * @param string $token
     * @return string
     */
    private function validateToken($token)
    {
        $http = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);
        $response = $http->get($this->endpoint . self::HEALTHCHECK_ENPOINT);
        $responseBody = $response->getJson();

        /**
         * if in the case of an expired token let us get a new one
         */
        if (!isset($responseBody['status'])) {
            $token = $this->doRequestToken();
        }

        return $token;
    }


    /**
     * Get endpoint
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}