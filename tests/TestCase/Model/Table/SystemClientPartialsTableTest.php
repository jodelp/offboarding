<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SystemClientPartialsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SystemClientPartialsTable Test Case
 */
class SystemClientPartialsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SystemClientPartialsTable
     */
    public $SystemClientPartials;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.SystemClientPartials',
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
        $config = TableRegistry::getTableLocator()->exists('SystemClientPartials') ? [] : ['className' => SystemClientPartialsTable::class];
        $this->SystemClientPartials = TableRegistry::getTableLocator()->get('SystemClientPartials', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SystemClientPartials);

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
