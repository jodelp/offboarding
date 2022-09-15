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
class MystaffClientsTable extends Table
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


        return $validator;
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

    /**
     * Get the shorthand
     * @return string
     */
    public function getShortHand($id)
    {
        $clientEntity = $this->find()->where(['client_id' => $id])->first();
        return $clientEntity;
    }

    /**
     * Get client details by short_code
     * @return entity
     */
    public function getClientByShortHand($shorthand = null)
    {
        $client = $this->find('all')
            ->where([
                'LOWER(shorthand)' => strtolower($shorthand)
            ])
            ->first();

        return $client ? $client : null;
    }

    /**
     * Get client details by name
     * @return entity
     */
    public function getClientByName($client_name = null)
    {
        $client = $this->find('all')
            ->where([
                'LOWER(name)' => strtolower($client_name)
            ])
            ->first();

        return $client ? $client : null;
    }

    /**
     * Get client details by client_id
     * @return entity
     */
    public function getClientByClientID($client_id = null, $parent_id = null)
    {
        $client = $this->find('all')
            ->where([
                'client_id' => $client_id,
                'parent_client_id' => $parent_id
            ])
            ->first();

        return $client ? $client : null;
    }

    /**
     * Check if client is a child client
     * @return boolean
     */
    public function isChildClient($parent_client_id = null, $child_client_id = null)
    {
        $client = $this->find('all')
            ->where([
                'client_id' => $child_client_id,
                'parent_client_id' => $parent_client_id
            ])
            ->first();

        return $client ? true : false;
    }


}
