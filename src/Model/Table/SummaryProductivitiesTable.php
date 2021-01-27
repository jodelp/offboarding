<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\I18n\Date;

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
        if (count($taskDescriptions) < 1) { // allow first in progress task
          return [
              'status' => 'Error',
              'message' => 'No Working Found',
              '_serialize' => ['status', 'message']
          ];
        }

        $taskData = [];
        $i = 0;
        foreach($taskDescriptions as $taskDescription ){ // code rework

                // check if task type is working
                if (strtolower($taskDescription['type']) == "working") {
                    // group task by description; add in progress to pending or resolved correspondingly
                    if($taskDescription['status'] == 'in progress'){ // initial task
                        // create taskData
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i] = [
                            'status' => $taskDescription['status'],
                            'start'  => $taskDescription['created'],
                            'end'   => new Date('NOW') // added for first task calc
                        ];
                    } else { // add end time and status if pending or resolved
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i]['end'] = $taskDescription['created'];
                        $taskData[$taskDescription['task_category'].'{*}'.$taskDescription['description']][$i]['status'] = $taskDescription['status'];
                        $i++;
                    }

                }
        }

        $records = [];
        $sumSpendTime = [];
        foreach($taskData as $key => $items){ // loop to format

            // check each item per task name
            foreach($items as $item){
                // if status is pending
                if($item['status'] == 'pending'){
                    $startDate = new \DateTime($item['start']->format($dateTimeFormat));
                    $endDate = new \DateTime($item['end']->format($dateTimeFormat));
                    $interval = date_diff($startDate, $endDate);
                    $pendingTime = strtotime($interval->format("%H:%I:%S"));
                    $sumSpendTime[] = strtotime($interval->format("%H:%I:%S")); // add to array for resolved spend time if ever its resolved in future.
                    $records[$key.'{*}working'] = [
                        'interval'  => date('H:i:s', $pendingTime),
                        'status'    => $item['status']
                    ];
                }
                // if resolved
                if($item['status'] == 'resolved'){
                    $startDate = new \DateTime($item['start']->format($dateTimeFormat));
                    $endDate = new \DateTime($item['end']->format($dateTimeFormat));
                    $interval = date_diff($startDate, $endDate);
                    $sumSpendTime[] = strtotime($interval->format("%H:%I:%S"));
                    $pendingTime = date('H:i:s',array_sum($sumSpendTime)); // sum up total spend include pending from previous task

                    $records[$key.'{*}resolved'] = [
                        'interval'  => $pendingTime,
                        'status'    => $item['status']
                    ];
                    unset($records[$key.'{*}working']); // remove pending once resolved

                }

                // if first task
                if($item['status'] == 'in progress'){
                    $startDate = new \DateTime($item['start']->format($dateTimeFormat));
                    $endDate = new \DateTime($item['end']->format($dateTimeFormat));
                    $interval = date_diff($startDate, $endDate);
                    $pendingTime = strtotime($interval->format("%H:%I:%S"));
                    $sumSpendTime[] = strtotime($interval->format("%H:%I:%S")); // add to array for resolved spend
                    $records[$key.'{*}working'] = [
                        'interval'  => date('H:i:s', $pendingTime),
                        'status'    => $item['status']
                    ];
                }
            }
            unset($sumSpendTime); // clean sum array
        }

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
