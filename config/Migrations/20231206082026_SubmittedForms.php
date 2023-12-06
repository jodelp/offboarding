<?php
use Migrations\AbstractMigration;

class SubmittedForms extends AbstractMigration
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
        $table = $this->table('subitted_forms');
        $table
            ->addColumn('user_id', 'string', [
                'default' => null,
            ])
            ->addColumn('form_id', 'string', [
                'default' => null,
            ])
            ->addColumn('subform_id', 'string', [
                'default' => null,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
            ])
            ->addColumn('remarks', 'text', [
                'default' => null,
            ])
            ->addColumn('assessed_by', 'string', [
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
        $this->table('submitted_forms')->drop()->save();
    }
}
