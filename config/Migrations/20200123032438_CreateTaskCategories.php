<?php
use Migrations\AbstractMigration;

/**
 * Task Categories table is being used in mystaff to keep track of task categories
 *
 * 1. this table can accomodate user define task categories from clients
 * 2. Also link to productivities table
 * 3. The counter-part table is CRUD. records can move dynamically
 *
 * TODO:
 * 1. provide api endpoint to update records in this table coming from mystaff may it be real-time or queued
 */
class CreateTaskCategories extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('task_categories');
        $table
            ->addColumn('name', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('client_id', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->create();

        /**
         * insert initial data for our table
         */
        $now = date('Y-m-d H:i:s');
        $data = [
            [
                'name' => 'general',
                'client_id' => 0,
                'created' => $now,
                'modified' => $now,
            ],
        ];

        $table->insert($data)->save();
    }


    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('task_categories')->drop()->save();
    }
}
