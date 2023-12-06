<?php
use Migrations\AbstractMigration;

class Users extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('users');
        $table
            ->addColumn('username', 'string', [
                'default' => null,
            ])
            ->addColumn('employee_id', 'string', [
                'default' => null,
            ])
            ->addColumn('first_name', 'integer', [
                'default' => null,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
            ])
            ->addColumn('designation', 'string', [
                'default' => null,
            ])
            ->addColumn('client_name', 'string', [
                'default' => null,
            ])
            ->addColumn('department', 'string', [
                'default' => null,
            ])
            ->addColumn('date_filed', 'datetime', [
                'default' => null,
            ])
            ->addColumn('training_date', 'datetime', [
                'default' => null,
            ])
            ->addColumn('start_date', 'datetime', [
                'default' => null,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
            ])
            ->addColumn('role', 'string', [
                'default' => null,
            ])
            ->addColumn('otp', 'string', [
                'default' => null,
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
        $this->table('users')->drop()->save();
    }
}
