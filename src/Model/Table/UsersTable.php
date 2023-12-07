<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Datasource\ConnectionManager;
Use Cake\Network\Email\Email;
/**
 * Users Model
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('employee_id')
            ->maxLength('employee_id', 255)
            ->requirePresence('employee_id', 'create')
            ->notEmptyString('employee_id');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 255)
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 255)
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name');

        // $validator
        //     ->scalar('designation')
        //     ->maxLength('designation', 255)
        //     ->requirePresence('designation', 'create')
        //     ->allowEmptyString('designation');

        $validator
            ->scalar('client_name')
            ->maxLength('client_name', 255)
            ->requirePresence('client_name', 'create')
            ->notEmptyString('client_name');

        // $validator
        //     ->scalar('department')
        //     ->maxLength('department', 255)
        //     ->requirePresence('department', 'create')
        //     ->allowEmptyString('department');          

        // $validator
        //     ->scalar('date_filed')
        //     ->maxLength('date_filed', 255)
        //     ->requirePresence('date_filed', 'create')
        //     ->allowEmptyString('date_filed');   

        // $validator
        //     ->scalar('training_date')
        //     ->maxLength('training_date', 255)
        //     ->requirePresence('training_date', 'create')
        //     ->allowEmptyString('training_date');
            
        // $validator
        //     ->scalar('start_date')
        //     ->maxLength('start_date', 255)
        //     ->requirePresence('start_date', 'create')
        //     ->allowEmptyString('start_date'); 

        // $validator
        //     ->scalar('status')
        //     ->maxLength('status', 255)
        //     ->requirePresence('status', 'create')
        //     ->allowEmptyString('status'); 

        $validator
            ->scalar('role')
            ->maxLength('role', 255)
            ->requirePresence('role', 'create')
            ->notEmptyString('role');

        $validator
            ->scalar('otp')
            ->maxLength('otp', 255)
            ->requirePresence('otp', 'create')
            ->allowEmptyString('otp');

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
        $rules->add($rules->isUnique(['username']));
        return $rules;
    }
}
