<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SummaryProductivitiesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SummaryProductivitiesTable Test Case
 */
class SummaryProductivitiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SummaryProductivitiesTable
     */
    public $SummaryProductivities;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.SummaryProductivities',
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
        $config = TableRegistry::getTableLocator()->exists('SummaryProductivities') ? [] : ['className' => SummaryProductivitiesTable::class];
        $this->SummaryProductivities = TableRegistry::getTableLocator()->get('SummaryProductivities', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SummaryProductivities);

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
