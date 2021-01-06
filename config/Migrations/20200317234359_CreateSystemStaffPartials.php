<?php
use Migrations\AbstractMigration;

class CreateSystemStaffPartials extends AbstractMigration
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
        $table = $this->table('system_staff_partials');
        $table
            ->addColumn('staff_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 25
            ])
            ->addColumn('remarks', 'text', [
                'default' => NULL,
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
        $this->table('system_staff_partials')->drop()->save();
    }
}
