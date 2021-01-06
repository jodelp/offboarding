<?php
use Migrations\AbstractMigration;

class CreateTasks extends AbstractMigration
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
        $table = $this->table('tasks');
        $table
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('task_category_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('assignee_type', 'integer', [
                'default' => 1,
                'signed' => false,
                'null' => false,
            ])
            ->addColumn('assign_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('is_deleted', 'string', [
                'default' => 'no',
                'limit' => 3,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true
            ]);
        $table->create();
    }

    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('tasks')->drop()->save();
    }
}
