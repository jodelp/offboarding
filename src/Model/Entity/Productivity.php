<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Entity\Traits\LowerCaseTrait;

/**
 * Productivity Entity
 *
 * @property int $id
 * @property int $staff_id
 * @property int $client_id
 * @property int $task_category_id
 * @property int $duration
 * @property string|null $type
 * @property string|null $description
 * @property string|null $status
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Staff $staff
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\TaskCategory $task_category
 */
class Productivity extends Entity
{
    use LowerCaseTrait;

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
        'client_id' => true,
        'task_category_id' => true,
        'duration' => true,
        'type' => true,
        'description' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
//        'staff' => true,
//        'client' => true,
//        'task_category' => true,
    ];
}
