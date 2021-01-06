<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SystemLog Entity
 *
 * @property int $id
 * @property string $performed_by
 * @property string $affected_entity
 * @property string $log
 * @property string|null $remarks
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class SystemLog extends Entity
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
        'performed_by' => true,
        'affected_entity' => true,
        'log' => true,
        'remarks' => true,
        'created' => true,
        'modified' => true,
    ];
}
