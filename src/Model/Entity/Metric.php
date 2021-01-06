<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Metric Entity
 *
 * @property int $id
 * @property int $client_id
 * @property int $assignee_type
 * @property int $assign_id
 * @property string $name
 * @property string $is_deleted
 * @property string|null $description
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\Assign $assign
 */
class Metric extends Entity
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
        'client_id' => true,
        'assignee_type' => true,
        'assign_id' => true,
        'name' => true,
        'is_deleted' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
        'client' => true,
        'assign' => true,
    ];
}
