<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ClientsWorkbenchSetting Entity
 *
 * @property int $id
 * @property int $client_id
 * @property int $screen_capture
 * @property int $idle_time_starts_after
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Client $client
 */
class ClientsWorkbenchSetting extends Entity
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
        'screen_capture' => true,
        'idle_time_starts_after' => true,
        'created' => true,
        'modified' => true,
        'client' => true,
    ];
}
