<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SummaryProductivity Entity
 *
 * @property int $id
 * @property int $staff_id
 * @property \Cake\I18n\FrozenDate|null $process_date
 * @property int|null $client_id
 * @property int|null $task
 * @property int|null $pending
 * @property int|null $constraint
 * @property string|null $accomplished_task
 * @property string|null $pending_task
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Staff $staff
 * @property \App\Model\Entity\Client $client
 */
class SummaryProductivity extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'staff_id' => true,
        'process_date' => true,
        'client_id' => true,
        'task' => true,
        'pending' => true,
        'constraint' => true,
        'accomplished_task' => true,
        'pending_task' => true,
        'created' => true,
        'modified' => true,
        // 'staff' => true,
        // 'client' => true,
    ];
}
