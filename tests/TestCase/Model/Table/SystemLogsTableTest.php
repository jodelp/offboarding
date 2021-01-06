<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SystemLogsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SystemLogsTable Test Case
 */
class SystemLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SystemLogsTable
     */
    public $SystemLogs;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.SystemLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('SystemLogs') ? [] : ['className' => SystemLogsTable::class];
        $this->SystemLogs = TableRegistry::getTableLocator()->get('SystemLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SystemLogs);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
