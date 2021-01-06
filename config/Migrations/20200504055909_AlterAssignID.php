<?php
use Migrations\AbstractMigration;

class AlterAssignID extends AbstractMigration
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
        $table = $this->table('metrics');
        $table->changeColumn('assign_id', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 255
        ])->save();
    }
}
