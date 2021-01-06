<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductivitiesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductivitiesTable Test Case
 */
class ProductivitiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductivitiesTable
     */
    public $Productivities;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Productivities',
        'app.Staffs',
        'app.Clients',
        'app.TaskCategories',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Productivities') ? [] : ['className' => ProductivitiesTable::class];
        $this->Productivities = TableRegistry::getTableLocator()->get('Productivities', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Productivities);

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
