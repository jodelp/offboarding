<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ClientsWorkbenchSettingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ClientsWorkbenchSettingsTable Test Case
 */
class ClientsWorkbenchSettingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ClientsWorkbenchSettingsTable
     */
    public $ClientsWorkbenchSettings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ClientsWorkbenchSettings',
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
        $config = TableRegistry::getTableLocator()->exists('ClientsWorkbenchSettings') ? [] : ['className' => ClientsWorkbenchSettingsTable::class];
        $this->ClientsWorkbenchSettings = TableRegistry::getTableLocator()->get('ClientsWorkbenchSettings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ClientsWorkbenchSettings);

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
