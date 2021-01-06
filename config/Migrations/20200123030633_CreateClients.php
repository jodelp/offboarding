<?php
use Migrations\AbstractMigration;

class CreateClients extends AbstractMigration
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
        $table = $this->table('clients');
        $table
            ->addColumn('name', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('short_code', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
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
    }


    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('clients')->drop()->save();
    }
}
