<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'employee_id' => 'CS-0001',
                'designation'  => 'admin',
                'client_name' => 'CS Modern Workforce',
                'department' => 'HR Offboarding',
                'first_name' => 'admin',
                'last_name' => 'admin',
                'role' => 'admin'
            ]
        ];

        $table = $this->table('users');
        $table->insert($data)->saveData();
    }
}
