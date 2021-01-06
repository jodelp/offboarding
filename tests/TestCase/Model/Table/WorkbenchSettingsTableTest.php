<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WorkbenchSettingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WorkbenchSettingsTable Test Case
 */
class WorkbenchSettingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WorkbenchSettingsTable
     */
    public $WorkbenchSettings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.WorkbenchSettings',
        'app.Staffs',
        'app.Clients',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('WorkbenchSettings') ? [] : ['className' => WorkbenchSettingsTable::class];
        $this->WorkbenchSettings = TableRegistry::getTableLocator()->get('WorkbenchSettings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->WorkbenchSettings);

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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
