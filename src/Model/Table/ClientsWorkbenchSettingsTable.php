<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ClientsWorkbenchSettings Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 *
 * @method \App\Model\Entity\ClientsWorkbenchSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ClientsWorkbenchSetting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ClientsWorkbenchSettingsTable extends Table
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

        $this->setTable('clients_workbench_settings');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->requirePresence('screen_capture', 'create')
            ->notEmptyString('screen_capture');

        $validator
            ->nonNegativeInteger('idle_time_starts_after')
            ->requirePresence('idle_time_starts_after', 'create')
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
        $rules->add($rules->existsIn(['client_id'], 'Clients'));

        return $rules;
    }
    
    public function getWorkbenchSettingsByClientId(int $clientId)
    {
        $data = $this->find('all')
            ->select()
            ->where(['client_id' => $clientId])
            ->first();

        return $data;
    }
}
