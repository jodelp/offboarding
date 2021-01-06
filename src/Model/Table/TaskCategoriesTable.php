<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

use App\Model\Entity\Client;

/**
 * TaskCategories Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 * @property \App\Model\Table\ProductivitiesTable&\Cake\ORM\Association\HasMany $Productivities
 *
 * @method \App\Model\Entity\TaskCategory get($primaryKey, $options = [])
 * @method \App\Model\Entity\TaskCategory newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TaskCategory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TaskCategory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TaskCategory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TaskCategory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TaskCategory[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TaskCategory findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaskCategoriesTable extends Table
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

        $this->setTable('task_categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('Productivities', [
            'foreignKey' => 'task_category_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');


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
        // $rules->add($rules->existsIn(['client_id'], 'Clients'));

        return $rules;
    }

    /**
     * Create or return the entity of the task categories
     * @param string $name
     * @return Cake\ORM|Entity
     */
    public function establish($name, Client $clientEntity)
    {
        $entity = $this->find('all')->where(['name' => $name])->first();

        /**
         * add the new user in our dataabase
         */
        if (empty($entity)) {
            $entity = $this->newEntity(
                [
                    'name' => $name,
                    'client_id' => $clientEntity->id
                ],
                ['validate' => false]
            );

            try {
                $this->saveOrFail($entity);
            } catch (PersistenceFailedException $ex) {
                Log::write(LogLevel::ERROR, 'Encountered while saving new client.');
                Log::write(LogLevel::ERROR, 'Payload' . $entity);
                Log::write(LogLevel::ERROR, $ex->getMessage());

                return null;
            }
        }

        return $entity;
    }
}
