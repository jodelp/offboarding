<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SystemLogs Model
 *
 * @method \App\Model\Entity\SystemLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\SystemLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SystemLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SystemLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SystemLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SystemLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SystemLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SystemLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SystemLogsTable extends Table
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

        $this->setTable('system_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('performed_by')
            ->maxLength('performed_by', 255)
            ->requirePresence('performed_by', 'create')
            ->notEmptyString('performed_by');

        $validator
            ->scalar('affected_entity')
            ->maxLength('affected_entity', 255)
            ->requirePresence('affected_entity', 'create')
            ->notEmptyString('affected_entity');

        $validator
            ->scalar('log')
            ->maxLength('log', 255)
            ->requirePresence('log', 'create')
            ->notEmptyString('log');

        $validator
            ->scalar('remarks')
            ->allowEmptyString('remarks');

        return $validator;
    }
}
