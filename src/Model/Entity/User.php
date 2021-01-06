<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Entity\Traits\LowerCaseTrait;

/**
 * Staff Entity
 *
 * @property int $id
 * @property string $username
 * @property int $client_id
 * @property string|null $last_name
 * @property string|null $first_name
 * @property int|null $lock
 * @property int|null $service_feedback
 * @property string|null $role
 * @property string|null $activation_key
 * @property int|null $active
 * @property int|null $subgroup_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 */
class User extends Entity
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
        'username' => true,
        'client_id' => true,
        'first_name' => true,
        'last_name' => true,
        'lock' => true,
        'service_feedback' => true,
        'role' => true,
        'activation_key' => true,
        'active' => true,
        'subgroup_id' => true,
    ];
}
