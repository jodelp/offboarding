<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SystemStaffPartial Entity
 *
 * @property int $id
 * @property int|null $staff_id
 * @property string|null $status
 * @property string|null $remarks
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Staff $staff
 */
class SystemStaffPartial extends Entity
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
        'status' => true,
        'remarks' => true,
        'created' => true,
        'modified' => true,
        'staff' => true,
    ];
}
