<?php
use Migrations\AbstractMigration;

class CreateStaffs extends AbstractMigration
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
        $table = $this->table('staffs');
        $table
            ->addColumn('employee_id', 'string', [
                'default' => null,
                'limit' => 80,
                'null' => true
            ])
            ->addColumn('username', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 80,
                'null' => true
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 80,
                'null' => true
            ])
            ->addColumn('middle_name', 'string', [
                'default' => null,
                'limit' => 80,
                'null' => true
            ])
            ->addColumn('gender', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => true
            ])
            ->addColumn('user_status', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true
            ])
            ->addColumn('employment_provider', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('group', 'string', [
                'default' => null,
                'limit' => 255,
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
            ])
            ->create();
    }


    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('staffs')->drop()->save();
    }
}
