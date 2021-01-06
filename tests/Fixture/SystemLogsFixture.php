<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SystemLogsFixture
 */
class SystemLogsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'performed_by' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'affected_entity' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'log' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'remarks' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'ascii_general_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => 'current_timestamp()', 'comment' => '', 'precision' => null],
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
                'performed_by' => 'Lorem ipsum dolor sit amet',
                'affected_entity' => 'Lorem ipsum dolor sit amet',
                'log' => 'Lorem ipsum dolor sit amet',
                'remarks' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2020-05-04 02:32:34',
                'modified' => '2020-05-04 02:32:34',
            ],
        ];
        parent::init();
    }
}
