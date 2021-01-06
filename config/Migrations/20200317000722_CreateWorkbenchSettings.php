<?php
use Migrations\AbstractMigration;

class CreateWorkbenchSettings extends AbstractMigration
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
        $table = $this->table('workbench_settings');
        $table
            ->addColumn('staff_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('screen_capture', 'integer', [
                'null' => false,
                'signed' => false,
                'default' => 0,
            ])
            ->addColumn('idle_time_starts_after', 'integer', [
                'null' => false,
                'signed' => false,
                'default' => 0,
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
}
