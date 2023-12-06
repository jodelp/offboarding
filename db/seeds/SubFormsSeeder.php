<?php


use Phinx\Seed\AbstractSeed;

class SubFormsSeeder extends AbstractSeed
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
            //Statutory Benefit Numbers
            [
                'parent_id' => 5,
                'name' => 'TIN',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 5,
                'name' => 'SSS No.',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 5,
                'name' => 'PHIC No.',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 5,
                'name' => 'HDMF No.',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],

            // DUES/PAYABLES
            [
                'parent_id' => 32,
                'name' => '-Remaining CForT',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'Remaining Workskill',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'Uniform Deduction',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'SSS Loan',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'HMDF Loan',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'AUFCOOP Loan',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'Cash Advances',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'parent_id' => 32,
                'name' => 'Uniform Deduction',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],

        ];

        $table = $this->table('subforms');
        $table->insert($data)->saveData();

    }
}
