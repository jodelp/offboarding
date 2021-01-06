<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

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
class MystaffWorkbenchSettingsTable extends Table
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

        $this->belongsTo('MystaffStaffs', [
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
     * Get the user ids of DAR Email Recipient
     * @return entity
     */
    public function getDAREmailRecipient_UserID($user_id = null, $client_id = null)
    {
        $recipient = $this->find('all')
            ->where([
                'staff_id' => $user_id,
                'client_id' => $client_id
            ])
            ->select(['dar_recipients'])
            ->first();

        return $recipient ? $recipient : null;
    }
}
