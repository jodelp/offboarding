<?php
use Migrations\AbstractMigration;

class Subforms extends AbstractMigration
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
        $table = $this->table('subforms');
        $table
            ->addColumn('parent_id', 'string', [
                'default' => null,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
            ])
            ->addColumn('user_id', 'string', [
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
        $this->table('subforms')->drop()->save();
    }
}
