<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Entity\Traits\LowerCaseTrait;

/**
 * Staff Entity
 *
 * @property int $id
 * @property string|null $employee_id
 * @property string $username
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $gender
 * @property string|null $user_status
 * @property string|null $employment_provider
 * @property string|null $group
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Employee $employee
 * @property \App\Model\Entity\Productivity[] $productivities
 */
class Staff extends Entity
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
        'employee_id' => true,
        'username' => true,
        'last_name' => true,
        'first_name' => true,
        'middle_name' => true,
        'gender' => true,
        'user_status' => true,
        'employment_provider' => true,
        'group' => true,
        'created' => true,
        'modified' => true,
//        'employee' => true,
//        'productivities' => true,
    ];
}
