<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TasksFixture
 */
class TasksFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'client_id' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'assignee_type' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '1', 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'assign_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'is_deleted' => ['type' => 'string', 'length' => 3, 'null' => false, 'default' => 'no', 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'timestamp', 'length' => null, 'null' => false, 'default' => 'current_timestamp()', 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'ascii_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd
    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'client_id' => 'Lorem ipsum dolor sit amet',
                'assignee_type' => 'Lorem ipsum dolor sit amet',
                'assign_id' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'is_deleted' => 'L',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1588575631,
                'modified' => '2020-05-04 07:00:31',
            ],
        ];
        parent::init();
    }
}
