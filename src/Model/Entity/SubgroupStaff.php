<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Entity\Traits\LowerCaseTrait;

/**
 * Staff Entity
 *
 * @property int $id
 * @property int $client_id
 * @property int $staff_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 */
class SubgroupStaff extends Entity
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
        'staff_id' => true,
    ];
}
