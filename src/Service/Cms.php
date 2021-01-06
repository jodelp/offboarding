<?php
namespace App\Service;

use Cake\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;

class Cms extends Token
{
    const CMS_ENDPOINT_FIELD = 'clients-microservice-endpoint';
    const CMS_ENDPOINT_STAFF_ENGAGEMENT = 'staff_engagement.json';
    const CMS_ENDPOINT_DETAILS = 'client/details.json';

    /**
     * Configurations Table
     * @var App\Model\Table\ConfigurationsTable
     */
    private $ConfigurationsTable;

    /**
     * Client microserive endpoint
     * @var string
     */
    private $endpoint;

    public function __construct()
    {
        parent::__construct();

        $this->ConfigurationsTable = TableRegistry::getTableLocator()->get('Configurations');

        $this->endpoint = $this->ConfigurationsTable->getConfig(self::CMS_ENDPOINT_FIELD);
    }

    /**
     * Get staff engagement information
     * @param string $username
     * @return Collection|boolean
     */
    public function getStaffEngagement($username)
    {
        $url = $this->endpoint.self::CMS_ENDPOINT_STAFF_ENGAGEMENT;
        $http = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $this->getToken()]
        ]);

        $response = $http->post($url, ['username' => $username]);
        $results = $response->getJson();

        if($results['status'] === 'Error') {
            return false;
        }

        return new Collection($results['engagements']);
    }


    /**
     * Get client details
     * @param string $shortCode
     * @return array
     */
    public function getDetails($shortCode)
    {
        if (empty($shortCode)) {
            return false;
        }

        $url = $this->endpoint.self::CMS_ENDPOINT_DETAILS;

        $http = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $this->getToken()]
        ]);
        $response = $http->get($url, ['client_code' => $shortCode]);
        $results = $response->getJson();

        if($results['status'] === 'Error') {
            return false;
        }

        return $results;
    }
}