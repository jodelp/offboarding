<?php
use Migrations\AbstractMigration;

class CreateSystemLogs extends AbstractMigration
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
        $table = $this->table('system_logs');
        $table
            ->addColumn('performed_by', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('affected_entity', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('log', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('remarks', 'text', [
                'null' => true,
                'default' => null
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
            ]);
        $table->create();
    }

    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('system_logs')->drop()->save();
    }
}
