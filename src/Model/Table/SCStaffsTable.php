<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class SCStaffsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * defaultConnectionName
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'cs_cib';
    }

    /**
     * get user
     * @param int $userId
     * @param string $date
     * @return string
     */
    public function getUserByUsername($username = null)
    {
        $data = $this->find()
            ->where([
                'username' => $username,
                'user_status' => 'active'
            ])
            ->first();

        return empty($data) ? null : $data;
    }

}
