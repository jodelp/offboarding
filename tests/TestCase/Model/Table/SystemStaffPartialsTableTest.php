<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SystemStaffPartialsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SystemStaffPartialsTable Test Case
 */
class SystemStaffPartialsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SystemStaffPartialsTable
     */
    public $SystemStaffPartials;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.SystemStaffPartials',
        'app.Staffs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('SystemStaffPartials') ? [] : ['className' => SystemStaffPartialsTable::class];
        $this->SystemStaffPartials = TableRegistry::getTableLocator()->get('SystemStaffPartials', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SystemStaffPartials);

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
