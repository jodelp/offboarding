<?php
use Migrations\AbstractMigration;

class CreateConfigurations extends AbstractMigration
{
    /**
     * This is our application configurations table
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
    $table = $this->table('configurations', [
            'collation' => 'utf8_general_ci'
        ]);
        $table
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'null' => false
            ])
            ->addColumn('status', 'enum', [
                'default' => null,
                'values' => ['active', 'inactive', 'delete']
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
            ])
            ->create();

        /**
         * insert initial data for our table
         */
        $data = [
            [
                'name' => 'identityserv-url',
                'value' => ''
            ],
            [
                'name' => 'identityserv-app-id',
                'value' => ''
            ],
            [
                'name' => 'identityserv-app-key',
                'value' => ''
            ],
        ];
        $table->insert($data)->save();
    }

    public function down()
    {
        $this->table('configurations')->drop()->save();
    }
}
