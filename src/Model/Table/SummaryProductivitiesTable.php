<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * SummaryProductivities Model
 *
 * @property \App\Model\Table\StaffsTable&\Cake\ORM\Association\BelongsTo $Staffs
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 *
 * @method \App\Model\Entity\SummaryProductivity get($primaryKey, $options = [])
 * @method \App\Model\Entity\SummaryProductivity newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SummaryProductivity[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SummaryProductivity|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SummaryProductivity saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SummaryProductivity patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SummaryProductivity[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SummaryProductivity findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SummaryProductivitiesTable extends Table
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

        $this->setTable('summary_productivities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Staffs', [
            'foreignKey' => 'staff_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Clients', [
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
            ->date('process_date')
            ->allowEmptyDate('process_date');

        $validator
            ->integer('task')
            ->allowEmptyString('task');

        $validator
            ->integer('pending')
            ->allowEmptyString('pending');

        $validator
            ->integer('constraint')
            ->allowEmptyString('constraint');

        $validator
            ->scalar('accomplished_task')
            ->allowEmptyString('accomplished_task');

        $validator
            ->scalar('pending_task')
            ->allowEmptyString('pending_task');

        return $validator;
    }

    public function computeSummary($staff, $startDate, $endDate)
    {
        $productivitiesTable = TableRegistry::getTableLocator()->get('Productivities');
        $summaryProductivitiesTable = TableRegistry::getTableLocator()->get('SummaryProductivities');
        $taskCategoryTable = TableRegistry::getTableLocator()->get('TaskCategories');
        $dateTimeFormat = 'Y-m-d H:i:s';

        /**
         * Notes for fetching productivities by date:
         * our application is saving information in UTC in order to fetch a PHT date
         * one must use date coverage for handling the request date. i.e 2020-03-10 PHT is equivalent to
         * From > 2020-03-09 16:00:00 (UTC) To > 2020-03-10 15:59:59 (UTC)
         */

        $productivities = $productivitiesTable->find()
            ->where([
                'staff_id' => $staff->id,
                "created BETWEEN '{$startDate}' AND '{$endDate}'"

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

        //get the id of the General type task category
        $task_category_gen_type = $taskCategoryTable->find()
            ->where([
                'name' => 'general'
            ])
            ->first()
            ->toArray();

        //get the client id
        $clientsTable = TableRegistry::getTableLocator()->get('Clients');
        $client = $clientsTable->get($productivities->first()['client_id']);

        $taskDesciptions= [];
        foreach ( $productivities as $productivity ) {

            $conditions = [
                'id' => $productivity['task_category_id'],
            ];

            //get task category using task_category_id and client_id
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
                'created' => $productivity->created
            ];

        }
        if (count($taskDescriptions) <= 1) {
          return [
              'status' => 'Error',
              'message' => 'No Working Found',
              '_serialize' => ['status', 'message']
          ];
        }
        $workingSpent = $working = 0;
        $workingDescriptions = $workingStatus = [];
        $startDateInc = 0;
        $endDateInc = 1;

        $records = [];
        foreach ($taskDescriptions as $taskDescription) {
            $startDate = new \DateTime($taskDescriptions[$startDateInc]['created']->format($dateTimeFormat));
            $endDate = new \DateTime($taskDescriptions[$endDateInc]['created']->format($dateTimeFormat));
            $interval = date_diff($startDate, $endDate);

            if (strtolower($taskDescription['type']) == "working") {
                // $working = $working + strtotime($interval->format("%H:%I:%S"));
                // if (!in_array($taskDescription['description'], $workingDescriptions)) {
                //     array_push($workingDescriptions, [ $taskDescription['task_category'] .'{*}'. $taskDescription['description'] => date('H:i:s', $workingSpent = strtotime($interval->format("%H:%I:%S")))] );
                //     array_push($workingStatus,[ $taskDescription['task_category'] .'{*}'. $taskDescription['description'] => $taskDescription['status'] ]);
                // }

                if(!array_key_exists($taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}working' , $records)) {

                    if(in_array($taskDescription['status'], ['pending', 'in progress'])) {

                        if(isset($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'].'{*}working']['interval'])){
                            $total_time = strtotime($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'].'{*}working']['interval']) + strtotime($interval->format("%H:%I:%S"));    

                        } else {
                            $total_time = strtotime($interval->format("%H:%I:%S"));    

                        }
                        
                        $records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}working'] = [
                            'interval'  => date('H:i:s', $total_time),
                            'status'    => $taskDescription['status']
                        ];    

                    } else {
                        $records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}resolved'] = [
                            'interval'  => date('H:i:s', strtotime($interval->format("%H:%I:%S"))),
                            'status'    => $taskDescription['status']
                        ];
                    }

                } else {

                    if($taskDescription['status'] == 'resolved') {
                            
                            $total_time = strtotime($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'].'{*}working']['interval']) + strtotime($interval->format("%H:%I:%S"));
                            unset($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}working']);

                            if(isset($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}resolved'])) {
                                $total_time = strtotime($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}resolved']['interval']) + $total_time;
                            }

                            $records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}resolved'] = [
                                'interval'  => date('H:i:s', $total_time),
                                'status'    => $taskDescription['status']
                            ];

                    } else {
                        $total_time = strtotime($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'].'{*}working']['interval']) + strtotime($interval->format("%H:%I:%S"));
                        unset($records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}working']);

                        $records[$taskDescription['task_category'] .'{*}'. $taskDescription['description'] .'{*}working'] = [
                            'interval'  => date('H:i:s', $total_time),
                            'status'    => $taskDescription['status']
                        ];
                    }

                    
                }

            }
            // $endDateInc++;
            // $startDateInc++;
            // if (count($taskDescriptions) == $endDateInc ) {
            //     $endDateInc--;
            // }

        }


//         $totalAccomplished = $sumStatus = 0;
//         $resolvedStatus = $pendingStatus = $allpending = $sumWorking = [];

//         foreach ($workingDescriptions as $item) {
//             $description = key($item);
//             $summary = current($item);
//             if(!isset($sumWorking[$description])){
//                     $sumWorking[$description] = 0;
//                 }
//                 $sumWorking[$description] += strtotime($summary);

//         }
//         $workingDescriptions = $sumWorking;

//         foreach ($workingStatus as $item) {
//             $description = key($item);
//             $summary = current($item);
//             if (array_key_exists($description,$item) && ($item[$description] == "pending")) {
//                   array_push($pendingStatus, $description);
//             }

//             if (array_key_exists($description,$item) && $item[$description] == "resolved") {
//                   array_push($resolvedStatus, $description);
//             }
//         }

//         //sort all pending status in resolved
//         $pendingSame = array_unique($pendingStatus);
//         $resolveSame = array_unique($resolvedStatus);
//         $pendingAll = array_diff($pendingSame, $resolvedStatus);
//         $penDingTask = $accomplishedTask = $pendingTask = [];

//         foreach (array_merge($pendingAll, $resolveSame) as $statusKey) {

//             $taskCategory = explode('{*}', $statusKey);

// //            $task_description = '';
// //            if (strpos($taskCategory[1], '|') !== false) {
// //                $data_description = explode('|', $taskCategory[1]);
// //                $task_description = $data_description[1];
// //
// //            } else {
// //                $task_description = $taskCategory[1];
// //            }

//             if (in_array($statusKey,$pendingAll)) {
//               array_push($penDingTask,  $dataPending = [
//                   'task_category' => $taskCategory[0],
//                   'name' => $taskCategory[1],
//                   'spent_time' => date('H:i:s',$workingDescriptions[$statusKey]),
//                   'constraints' => 0,
//                 ]);
//             }
//             if (in_array($statusKey,$resolveSame)) {
//               array_push($accomplishedTask,  $dataAccomplished = [
//                   'task_category' => $taskCategory[0],
//                   'name' => $taskCategory[1],
//                   'spent_time' => date('H:i:s',$workingDescriptions[$statusKey]),
//                   'constraints' => 0,
//                 ]);
//             }
//         }

        $count_resolved     = 0;
        $count_pending      = 0;
        $allPendingTasks    = [];
        $allResolvedTasks   = [];
        $pendingAll = ['pending', 'in progress'];
        if($records) {
            foreach($records as $key => $val) {
                
                $taskCategory = explode('{*}', $key);

                if ($val['status'] != 'resolved') {
                    array_push($allPendingTasks,  $dataPending = [
                        'task_category' => $taskCategory[0],
                        'name' => $taskCategory[1],
                        'spent_time' => $val['interval'],
                        'constraints' => 0,
                    ]);

                    $count_pending++;
                }  
                if ($val['status'] == 'resolved') {
                    array_push($allResolvedTasks,  $dataAccomplished = [
                        'task_category' => $taskCategory[0],
                        'name' => $taskCategory[1],
                        'spent_time' => $val['interval'],
                        'constraints' => 0,
                    ]);

                    $count_resolved++;
                }
                
            }
        }

        $summaryProductivityEntity = $summaryProductivitiesTable->newEntity([
            'staff_id' => $staff->id,
            'process_date' => date('Y-m-d', $endDate->getTimestamp()),
            'client_id' => $client->id,
            'task' => $count_resolved,
            'pending' => $count_pending,
            'accomplished_task' => empty($allResolvedTasks) ? NULL : json_encode($allResolvedTasks),
            'pending_task' => empty($allPendingTasks) ? NULL : json_encode($allPendingTasks)
        ]);

        return $summaryProductivityEntity;
    }
}
