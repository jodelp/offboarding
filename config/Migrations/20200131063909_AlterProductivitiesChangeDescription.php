<?php
use Migrations\AbstractMigration;

class AlterProductivitiesChangeDescription extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Update the productivities table change the column description from string 255 to text
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $table = $this->table('productivities');

        $table->changeColumn('description', 'text', [
            'default' => null,
            'null' => true,
        ])->save();
    }
}
