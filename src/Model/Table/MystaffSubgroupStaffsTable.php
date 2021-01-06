<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SubgroupStaff Model
 *
 * @method \App\Model\Entity\SubgroupStaff get($primaryKey, $options = [])
 * @method \App\Model\Entity\SubgroupStaff newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SubgroupStaff[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SubgroupStaff|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubgroupStaff saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubgroupStaff patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SubgroupStaff[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SubgroupStaff findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MystaffSubgroupStaffsTable extends Table
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

        $this->setTable('subgroup_staff');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

    }

    /**
     * defaultConnectionName
     * @return string
     */
    public static function defaultConnectionName()
    {
        /**
         * set the namespace to a default value. this is due to the fact that
         * when Models are loaded in Command/Console there is no Cache::read('namespace') value for this
         * This is used at MigrateClientsRecordsCommand script
         */

        return 'mystaff';
    }

    public function getStaffSubgroup($staff_id = null)
    {
        $subgroupEntity = $this->find('all')
            ->where(['staff_id' => $staff_id])
            ->first();

        return $subgroupEntity ? $subgroupEntity : null;

    }

}
