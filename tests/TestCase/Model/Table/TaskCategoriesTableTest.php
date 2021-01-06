<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TaskCategoriesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TaskCategoriesTable Test Case
 */
class TaskCategoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TaskCategoriesTable
     */
    public $TaskCategories;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.TaskCategories',
        'app.Clients',
        'app.Productivities',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('TaskCategories') ? [] : ['className' => TaskCategoriesTable::class];
        $this->TaskCategories = TableRegistry::getTableLocator()->get('TaskCategories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TaskCategories);

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
