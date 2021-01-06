<?php
use Migrations\AbstractMigration;

class CreateClientsWorkbenchSettings extends AbstractMigration
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
        $table = $this->table('clients_workbench_settings');
        $table
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('screen_capture', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('idle_time_starts_after', 'integer', [
                'null' => false,
                'signed' => false,
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
        $this->table('clients_workbench_settings')->drop()->save();
    }
}
