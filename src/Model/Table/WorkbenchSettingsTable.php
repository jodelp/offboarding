<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

use App\Model\Entity\Staff;
use App\Model\Entity\Client;

/**
 * WorkbenchSettings Model
 *
 * @property \App\Model\Table\StaffsTable&\Cake\ORM\Association\BelongsTo $Staffs
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 *
 * @method \App\Model\Entity\WorkbenchSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\WorkbenchSetting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\WorkbenchSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WorkbenchSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkbenchSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkbenchSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WorkbenchSetting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\WorkbenchSetting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorkbenchSettingsTable extends Table
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

        $this->setTable('workbench_settings');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Staffs', [
            'foreignKey' => 'staff_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->nonNegativeInteger('screen_capture')
            ->notEmptyString('screen_capture');

        $validator
            ->nonNegativeInteger('idle_time_starts_after')
            ->notEmptyString('idle_time_starts_after');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['staff_id'], 'Staffs'));
        $rules->add($rules->existsIn(['client_id'], 'Clients'));

        return $rules;
    }


    /**
     * Fetch the workbench settings for this staff and client
     * @param Staff $staffEntity
     * @param Client $clientEntity
     * @return Array | Boolean
     */
    public function getSettings(Staff $staffEntity, Client $clientEntity)
    {
        $the = $this->find()
            ->where([
                'staff_id' => $staffEntity->id,
                'client_id' => $clientEntity->id
            ])
            ->first();

        return ($the) ? ['screen_capture' => $the->screen_capture, 'idle_time_starts_after' => $the->idle_time_starts_after] : false;
    }
}
