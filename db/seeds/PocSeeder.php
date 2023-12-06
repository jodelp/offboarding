<?php


use Phinx\Seed\AbstractSeed;

class PocSeeder extends AbstractSeed
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
                'username' => 'hrpoc',
                'employee_id' => 'CS-0002',
                'poc_email' => 'hrpoc@cloudstaff.com',
                'first_name'  => 'hrfirstname',
                'last_name' => 'hrlastname',
                'department_id' => 1,
                'created_by' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'itpoc',
                'employee_id' => 'CS-0003',
                'poc_email' => 'itpoc@cloudstaff.com',
                'first_name'  => 'itfirstname',
                'last_name' => 'itlastname',
                'department_id' => 2,
                'created_by' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'designatedpoc',
                'employee_id' => 'CS-0004',
                'poc_email' => 'designatedpoc@cloudstaff.com',
                'first_name'  => 'designatedfirstname',
                'last_name' => 'designatedlastname',
                'department_id' => 3,
                'created_by' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'financepoc',
                'employee_id' => 'CS-0005',
                'poc_email' => 'financepoc@cloudstaff.com',
                'first_name'  => 'financefirstname',
                'last_name' => 'financelastname',
                'department_id' => 4,
                'created_by' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],

        ];

        $table = $this->table('pocs');
        $table->insert($data)->saveData();
    }
}
