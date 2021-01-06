<?php
use Migrations\AbstractMigration;

class CreateSummaryProductivities extends AbstractMigration
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
        $table = $this->table('summary_productivities');
        $table
            ->addColumn('staff_id', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('process_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addColumn('client_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addColumn('task', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addColumn('pending', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addColumn('constraint', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addColumn('accomplished_task', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('pending_task', 'text', [
                'default' => null,
                'null' => true
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
            ->addIndex('staff_id', [
                'name' => 'idx_staff_id'
            ])
            ->addIndex('process_date', [
                'name' => 'idx_process_date'
            ])
            ->addIndex('client_id', [
                'name' => 'idx_client_id'
            ]);

        $table->create();
    }


      /**
       * remove table when when rollback is called
       * @return void
       */
      public function down(): void
      {
          $this->table('summary_productivities')->drop()->save();
      }
}
