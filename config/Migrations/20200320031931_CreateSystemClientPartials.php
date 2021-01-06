<?php
use Migrations\AbstractMigration;

class CreateSystemClientPartials extends AbstractMigration
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
        $table = $this->table('system_client_partials');
        $table
            ->addColumn('client_id', 'integer', [
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
        $this->table('system_client_partials')->drop()->save();
    }
}
