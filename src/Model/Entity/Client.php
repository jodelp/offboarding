<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Entity\Traits\LowerCaseTrait;

/**
 * Client Entity
 *
 * @property int $id
 * @property string $name
 * @property string $short_code
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Productivity[] $productivities
 * @property \App\Model\Entity\TaskCategory[] $task_categories
 */
class Client extends Entity
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
        'name' => true,
        'short_code' => true,
        'created' => true,
        'modified' => true,
        'clients_workbench_settings' => true,
//        'productivities' => true,
//        'task_categories' => true,
    ];
}
