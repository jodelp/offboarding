<?php


use Phinx\Seed\AbstractSeed;

class AddCmsToConfigurations extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'name' => 'clients-microservice-endpoint',
                'value' => ''
            ]
        ];

        $table = $this->table('configurations');
        $table->insert($data)->saveData();
    }
}
