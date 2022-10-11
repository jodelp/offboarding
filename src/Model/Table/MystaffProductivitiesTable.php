<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\I18n\Date;
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
class MystaffProductivitiesTable extends Table
{

    const IN_PROGRESS = 'In Progress';
    const RESOLVED = 'Resolved';
    const PENDING = 'Pending';
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('productivities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Staff', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('TaskCategory', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('ActivityConstraint', [
            'foreignKey' => 'productivity_id',
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


    public function afterFind($results, $primary = false) {
        $prevStartDate = "";

        date_default_timezone_set("UTC");
        $current_date = strtotime(date('Y-m-d H:i:s'));

	    foreach ($results as $key => $val) {
	        if (isset($val['Productivity']['created'])) {

                $created = strtotime($val['Productivity']['created']);

                if(isset($val['Productivity']['type'])){

                    if ($val['Productivity']['type'] == 'break') {
                        $prevStartDate = date('Y-m-d H:i:s', $created);
                    }

                    if ($val['Productivity']['type'] == 'idle') {
                        $prevStartDate = date('Y-m-d H:i:s', $created);
                    }

                    if ($val['Productivity']['type'] == 'out') {
                        $prevStartDate = date('Y-m-d H:i:s', $created);
                    }

                    if ($val['Productivity']['type'] == 'stopped') {
                        $prevStartDate = date('Y-m-d H:i:s', $created);
                    }

                    if(isset($val['Productivity']['status'])){
                        if (ucfirst($val['Productivity']['status']) == 'Resolved') {
                            $prevStartDate = date('Y-m-d H:i:s', $created);
                        }
                    }
                }

                $startDate  = date('Y-m-d H:i:s', $created);
                $endDate    = date('Y-m-d H:i:s', $created);

                if(!empty($prevStartDate)){
                    $endDate2 = $prevStartDate;
                }else{
                    $endDate2 = date('Y-m-d H:i:s', strtotime("+1 minutes", $current_date));
                    // $endDate2 = date('Y-m-d H:i:s');
                }


                $start          = new DateTime($startDate);
                $end            = new DateTime($endDate2);
                $interval       = $start->diff($end);

                $total_hour     = ($interval->format('%h'))? $interval->format('%h') : "00";
                $total_minute   = $interval->format('%i');
                // $total_minute   = ($interval->format('%i') <= 1) ? $interval->format('%i') : $interval->format('%i');

                $results[$key]['Productivity']['timespent'] = $total_hour.':'.$total_minute;
                $results[$key]['Productivity']['date'] = CakeTime::format(DATE_FORMAT, strtotime($val['Productivity']['created']));
                $results[$key]['Productivity']['time'] = CakeTime::format('H:i:s', strtotime($val['Productivity']['created']));

                // $results[$key]['Productivity']['created'] = $this->dateFormatAfterFind(
                //     $val['Productivity']['created']
                // );

                $prevStartDate = $startDate;

            }
        }

	    return $results;
	}

	public function dateFormatAfterFind($dateString) {
		return CakeTime::format('M d, Y H:i', strtotime($dateString));
	}

	protected function _findTop($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions']['Article.published'] = true;
            return $query;
        }
        return $results;
    }

    public function computeSummary($staff, $startDate, $endDate)
    {
        $productivitiesTable = TableRegistry::getTableLocator()->get('MystaffProductivities');
        $summaryProductivitiesTable = TableRegistry::getTableLocator()->get('SummaryProductivities');
        $taskCategoryTable = TableRegistry::getTableLocator()->get('MystaffTaskCategories');
        $dateTimeFormat = 'Y-m-d H:i:s';

        /**
         * Notes for fetching productivities by date:
         * our application is saving information in UTC in order to fetch a PHT date
         * one must use date coverage for handling the request date. i.e 2020-03-10 PHT is equivalent to
         * From > 2020-03-09 16:00:00 (UTC) To > 2020-03-10 15:59:59 (UTC)
         */

        $productivities = $productivitiesTable->find()
            ->where([
                'user_id' => $staff->id,
                "created BETWEEN '{$startDate}' AND '{$endDate}'",
                'status !=' => 'delete'
            ])
            ->order(['id' => 'ASC'])
            ->all();

        // if empty productivities
        if (empty($productivities->toArray())) {
            return [
                'status' => 'Error',
                'message' => 'No Productivity Found',
                '_serialize' => ['status', 'message']
            ];
        }

        //get the client id
        $clientsTable = TableRegistry::getTableLocator()->get('MystaffClients');
        $client = $clientsTable->find()->where(['client_id' => $productivities->first()['client_id']])->first();

        $taskDesciptions= [];
        foreach ( $productivities as $productivity ) {

            $conditions = [
                'id' => $productivity['category_id'],
            ];

            //get task category using category_id and client_id
            $task_category_details = $taskCategoryTable->find()
                ->where([
                    $conditions
                ])
                ->order(['id' => 'ASC'])
                ->first();

            $taskDescriptions[] = [
                'task_category' => $task_category_details ? ucwords($task_category_details['name']) : '',
                'description' => $productivity->description,
                'type' => $productivity->type,
                'status' => $productivity->status,
                'created' => $productivity->created,
                'duration' => $productivity->duration
            ];

        }
        if (count($taskDescriptions) < 1) { // allow first in progress task
          return [
              'status' => 'Error',
              'message' => 'No Working Found',
              '_serialize' => ['status', 'message']
          ];
        }

        $taskData = [];
        $i = 0;
        $iStart = 0;
        $ctr = 0;
        foreach($taskDescriptions as $taskDescription ){ // code rework
                // check if task type is working
                if (strtolower($taskDescription['type']) == "working") {
                    // group task by description; add in progress to pending or resolved correspondingly
                    if($taskDescription['status'] == SELF::IN_PROGRESS || $taskDescription['status'] == SELF::PENDING){ // initial task
                        // create taskData
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i] = [
                            'status' => $taskDescription['status'],
                            'start'  => $taskDescription['created'],
                            'end'   => new Date('NOW'), // added for in progress task
                            'duration' => $ctr == 0 || !isset($taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i]) ? $taskDescription['duration'] : $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i]['duration'] + $taskDescription['duration']
                        ];
                        $iStart = $i; // Attaching index

                    }
                    else {
                        // add end time and status if pending or resolved
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$iStart]['end'] = $taskDescription['created'];
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$iStart]['status'] = $taskDescription['status'];
                        $i++;
                    }
                }
                $ctr++;
        }
        $records = [];
        foreach($taskData as $key => $items){ // loop to format
            if($items){
                // check each item per task name
                foreach($items as $item){
                    // if status is pending
                    if($item['status'] == SELF::PENDING){
                        $interval = $item['duration']/60;
                        $records[$key.'{*}working'] = [
                            'interval'  =>  (!isset($records[$key.'{*}working']['interval']) ? 0 : $records[$key.'{*}working']['interval']) + $interval,
                            'status'    => $item['status']
                        ];
                    }
                    // if resolved
                    if($item['status'] == SELF::RESOLVED){
                        $interval = $item['duration']/60;
                        $records[$key.'{*}resolved'] = [
                            'interval'  => ((!isset($records[$key.'{*}resolved']['interval']) ? 0 : $records[$key.'{*}resolved']['interval']) + $interval) + (!isset($records[$key.'{*}working']) ? 0 : $records[$key.'{*}working']['interval']),
                            'status'    => $item['status']
                        ];
                        unset($records[$key.'{*}working']); // remove pending once resolved

                    }

                    // if first task
                    if($item['status'] == SELF::IN_PROGRESS){
                        $interval = $item['duration']/60;
                        $records[$key.'{*}working'] = [
                            'interval'  => (!isset($records[$key.'{*}working']['interval']) ? 0 : $records[$key.'{*}working']['interval']) + $interval,
                            'status'    => $item['status']
                        ];
                    }
                }
            }
        }
        if (empty($records)) { // if empty records was generate, due to incomplete data
          return [
              'status' => 'Error',
              'message' => 'No Working Found',
              '_serialize' => ['status', 'message']
          ];
        }
        $count_resolved     = 0;
        $count_pending      = 0;
        $allPendingTasks    = [];
        $allResolvedTasks   = [];
        $pendingAll = [SELF::PENDING, SELF::IN_PROGRESS];
        if($records) {
            foreach($records as $key => $val) {

                $taskCategory = explode('{*}', $key);

                if ($val['status'] != SELF::RESOLVED) {
                    array_push($allPendingTasks,  $dataPending = [
                        'task_category' => $taskCategory[0],
                        'name' => $taskCategory[1],
                        'spent_time' => round($val['interval'], 2),
                        'constraints' => 0,
                    ]);

                    $count_pending++;
                }
                if ($val['status'] == SELF::RESOLVED) {
                    array_push($allResolvedTasks,  $dataAccomplished = [
                        'task_category' => $taskCategory[0],
                        'name' => $taskCategory[1],
                        'spent_time' => round($val['interval'], 2),
                        'constraints' => 0,
                    ]);

                    $count_resolved++;
                }


            }
        }
        $summaryProductivityEntity = $summaryProductivitiesTable->newEntity([
            'staff_id' => $staff->id,
            'process_date' => date('Y-m-d',strtotime($endDate)),
            'client_id' => $client->client_id,
            'task' => $count_resolved,
            'pending' => $count_pending,
            'accomplished_task' => empty($allResolvedTasks) ? NULL : json_encode($allResolvedTasks),
            'pending_task' => empty($allPendingTasks) ? NULL : json_encode($allPendingTasks)
        ]);
        $summaryProductivityEntity['client_name'] = $client->name;
        return $summaryProductivityEntity;
    }


}
