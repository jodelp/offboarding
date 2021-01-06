<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Staffs Model
 *
 * @property \App\Model\Table\EmployeesTable&\Cake\ORM\Association\BelongsTo $Employees
 * @property \App\Model\Table\ProductivitiesTable&\Cake\ORM\Association\HasMany $Productivities
 *
 * @method \App\Model\Entity\Staff get($primaryKey, $options = [])
 * @method \App\Model\Entity\Staff newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Staff[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Staff|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Staff saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Staff patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Staff[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Staff findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StaffsTable extends Table
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

        $this->setTable('staffs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

//        $this->hasMany('Productivities', [
//            'foreignKey' => 'staff_id',
//        ]);
        $this->hasOne('SystemStaffPartials', [
            'foreignKey' => 'staff_id',
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
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

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

    /**
     * Create or return the entity of the staff
     * @param string $username
     * @param boolean $create Flags the creation/addition of none existing staffs
     * @return Cake\ORM|Entity
     */
    public function establish($username, $create = true)
    {
        $entity = $this->find('all')->where(['username' => $username])->first();

        /**
         * add the new user in our dataabase
         */
        if (empty($entity) and $create) {
            $entity = $this->newEntity(['username' => $username]);

            try {
                $this->saveOrFail($entity);
            } catch (PersistenceFailedException $ex) {
                Log::write(LogLevel::ERROR, 'Encountered while saving new staff.');
                Log::write(LogLevel::ERROR, 'Payload' . $entity);
                Log::write(LogLevel::ERROR, $ex->getMessage());

                return null;
            }
        }

        return $entity;
    }

    /**
     * Find all blank employee id
     * @param Query $query
     * @param array $options
     * @return Cake\ORM|Query
     */
    public function findBlankEmployeeId(Query $query, array $options)
    {
        return $query->where(['OR' => [
            'employee_id is null',
            'employee_id' => ''
            ]
        ]);
    }

    /**
    * Returns the Staff Entity by username
    * @param string $username
    * @return Entity
    * @throws UnexpectedValueException
    */
    public function getIdByUsername($username)
    {
        if (empty($username)) {
            throw new UnexpectedValueException('Username cannot be blank');
        }

        $entity = $this->find()
                ->where(function (\Cake\Database\Expression\QueryExpression $exp, \Cake\ORM\Query $q) use ($username) {
                    return $exp->like('username', "{$username}%");
                })
                ->andWhere([
                    'id <>' => 0
                ])
                ->first();

        return empty($entity) ? null : $entity;
    }

    /**
     * get all staffs
     * used in CompleteStaffsRecordsCommand
     */
    public function getAll(){

        $list = $this->find('all')
            ->select()
            ->contain(['SystemStaffPartials'])
            ->order(['Staffs.id' => 'desc'])
            ->all();

        return $list;
    }
}
