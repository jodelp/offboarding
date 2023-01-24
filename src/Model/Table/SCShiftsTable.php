<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class SCShiftsTable extends Table
{
    const TIMEZONE_CODE_PH = 'PH';
    const TIMEZONE_CODE_US = 'US';
    const TIMEZONE_CODE_UK = 'UK';


    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('shifts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * defaultConnectionName
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'cs_cib';
    }

    /**
     * Find shift by date
     * @param Query $query
     * @param array $options
     * @return Query $query
     */
    public function findByDate($query, $options)
    {
        return $query->where([
                'shift_date' => $options['date'],
                'user_id' => $options['user_id']
            ]
        );
    }

    /**
     * get timezone
     * @param int $userId
     * @param string $date
     * @return string
     */
    public function getTimezone($userId, $date): string
    {
        $query = $this->find('byDate', [
            'date' => $date,
            'user_id' => $userId
        ]);
        $entity = $query->first();

        return $this->setTimezone($entity->timezone);
    }

    /**
     * Return the timezone
     * @param string $tzCode
     * @return string
     */
    private function setTimezone($tzCode): string
    {
        $tz = '';

        switch ($tzCode) {
            case self::TIMEZONE_CODE_UK:
                $tz = 'Europe/London';
                break;
            case self::TIMEZONE_CODE_US:
                $tz = 'America/New_York';
                break;
            case self::TIMEZONE_CODE_PH:
            default:
                $tz = 'Asia/Manila';
                break;
        }

        return $tz;
    }

    /**
     * get shift details on a given date
     * @param int $userId
     * @param string $date
     * @return array
     */
    public function getShiftDetails($userId, $date): array
    {
        $data = $this->find('byDate', [
            'date' => $date,
            'user_id' => $userId
            ])
            ->last();

        if(!$data) {
            $data = $this->find()
                ->where([
                    'user_id' => $userId
                ])
                ->last();
        }

        $record = [];
        if($data) {
            $record = $data->toArray();
            $record['current_timezone'] = $this->setTimezone($data['timezone']);
        }

        return $record;
    }
}
