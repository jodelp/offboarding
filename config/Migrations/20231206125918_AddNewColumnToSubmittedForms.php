<?php
use Migrations\AbstractMigration;

class AddNewColumnToSubmittedForms extends AbstractMigration
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
        $table = $this->table('submitted_forms');
        $table->addColumn('poc_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
