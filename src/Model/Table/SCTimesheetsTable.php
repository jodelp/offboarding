<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Core\Exception\Exception;
use Cake\ORM\TableRegistry;

/**
 * Timesheets Model
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SCTimesheetsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('timesheets');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
     * Get timelogs on a given date
     * @param type $userId
     * @return Entity
     */
    public function getTimelogs($user_id = null, $date = null)
    {
        $data = $this->find()
            ->where([
                'user_id' => $user_id,
                'DATE(created)' => $date
            ])
            ->order(['id' => 'asc']);

        return empty($data) ? [] : $data->toArray();
    }
}
