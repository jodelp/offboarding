<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Clients Model
 *
 * @property \App\Model\Table\ProductivitiesTable&\Cake\ORM\Association\HasMany $Productivities
 * @property \App\Model\Table\TaskCategoriesTable&\Cake\ORM\Association\HasMany $TaskCategories
 *
 * @method \App\Model\Entity\Client get($primaryKey, $options = [])
 * @method \App\Model\Entity\Client newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Client[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Client|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Client saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Client patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Client[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Client findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ClientsTable extends Table
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

        $this->setTable('clients');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Productivities', [
            'foreignKey' => 'client_id',
        ]);
        $this->hasMany('TaskCategories', [
            'foreignKey' => 'client_id',
        ]);

        $this->hasOne('SystemClientPartials', [
            'foreignKey' => 'client_id',
        ]);

        $this->hasOne('ClientsWorkbenchSettings', [
            'foreignKey' => 'client_id',
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

        // $validator
        //     ->scalar('short_code')
        //     ->maxLength('short_code', 255)
        //     ->requirePresence('short_code', 'create')
        //     ->notEmptyString('short_code');

        return $validator;
    }


    /**
     * Create or return the entity of the client
     * @param string $clientName
     * @param string $clientCode (optional), process code first
     * @return Cake\ORM|Entity
     */
    public function establish($clientName, $clientCode = null)
    {
        //client name
        $client_entity = $this->find('all')->where(['LOWER(name)' => strtolower($clientName)])->first();
        $entity = false;
        if($client_entity) {
            if(!$client_entity->short_code) {
                if(!empty($clientCode)) {

                    $data = [
                            'short_code' => $clientCode
                        ];

                    $entity = $this->patchEntity($client_entity, $data);

                    try {
                        $this->saveOrFail($entity);
                    } catch (PersistenceFailedException $ex) {
                        Log::write(LogLevel::ERROR, 'Encountered while saving new client.');
                        Log::write(LogLevel::ERROR, 'Payload' . $entity);
                        Log::write(LogLevel::ERROR, $ex->getMessage());

                        return null;
                    }
                }
            }
        } else {
            if (!empty($clientCode)) {
                //client code
                $entity = $this->find('all')->where(['short_code' => $clientCode])->first();

                if (empty($entity)) {
                    $entity = $this->newEntity(
                        [
                            'name' => empty($clientName) ? '' : $clientName,
                            'short_code' => $clientCode
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
            } else {
                //client name
                $entity = $this->find('all')->where(['name' => $clientName])->first();

                if (empty($entity)) {
                    $entity = $this->newEntity(
                        ['name' => $clientName],
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
            }
        }

        return $entity ? $entity : $client_entity;
    }

    /**
     * get all clients
     * used in CompleteClientsRecordsCommand
     */
    public function getAll(){

        $list = $this->find('all')
            ->select()
            ->contain(['SystemClientPartials'])
            ->order(['Clients.id' => 'desc'])
            ->all();

        return $list;
    }


}
