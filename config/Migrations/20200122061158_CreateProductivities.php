<?php
use Migrations\AbstractMigration;

class CreateProductivities extends AbstractMigration
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
        $table = $this->table('productivities');
        $table
            ->addColumn('staff_id', 'integer', [
                'default' => 0,
            ])
            ->addColumn('client_id', 'integer', [
                'default' => 0,
            ])
            ->addColumn('task_category_id', 'integer', [
                'default' => 0,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'null' => true,
                'limit' => 80
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'null' => true,
                'limit' => 255
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'null' => true,
                'limit' => 80
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->create();
    }

    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('productivities')->drop()->save();
    }
}
