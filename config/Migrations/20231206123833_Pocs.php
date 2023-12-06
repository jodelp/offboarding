<?php
use Migrations\AbstractMigration;

class Pocs extends AbstractMigration
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
        $table = $this->table('pocs');
        $table
            ->addColumn('username', 'string', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('employee_id', 'string', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('poc_email', 'string', [
                'default' => null,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
            ])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'null' => true
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
        $this->table('pocs')->drop()->save();
    }
}
