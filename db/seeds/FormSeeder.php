<?php


use Phinx\Seed\AbstractSeed;

class FormSeeder extends AbstractSeed
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
            //HUMAN RESOURCES DEPARTMENT Forms
            [
                'department_id' => 1,
                'name' => 'Official Letter (Resignation Letter/Notice)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Acceptance Letter',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Non-Involvement Agreement and/or Termination Notice Reminders',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Exit Interview',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Statutory Benefit Numbers',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Company or Trainee ID',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Office key No./RFID card',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Cloudstaff Rewards Card',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Company car',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Username (UN) and Password (PW)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'HMO - Principal Card and Dependent\'s Card (if any) (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'ER2 - Separation Notice (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'CForT Agreement: (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Workskill Agreement: (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Last DTR Report: (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Summary of Holiday Credits: (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 1,
                'name' => 'Summary of Leave Credits: (HR Portion)',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            //end of HUMAN RESOURCES DEPARTMENT Forms

            //IT SUPPORT DEPARTMENT Forms
            [
                'department_id' => 2,
                'name' => 'Monitor',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'CPU',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'Mouse',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'Keyboard',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'Headset',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'Printer',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'AVR',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'AWS',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'VPN',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 2,
                'name' => 'Mobile Phone',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            //end of IT SUPPORT DEPARTMENT Forms

            //DESIGNATED DEPARTMENT Forms
            [
                'department_id' => 3,
                'name' => 'Team Lead / Department Head',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            //end of DESIGNATED DEPARTMENT Forms

            //FINANCE DEPARTMENT Forms
            [
                'department_id' => 4,
                'name' => 'Locker key',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 4,
                'name' => 'Licenses and Onlince Access',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 4,
                'name' => 'ATM Paycard (Proof of Closure)',
                'is_parent' => 'yes',
                'user_id' => 4,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'department_id' => 4,
                'name' => 'DUES/ PAYABLES',
                'is_parent' => 'yes',
                'user_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            //end of FINANCE DEPARTMENT Forms
        ];

        $table = $this->table('forms');
        $table->insert($data)->saveData();
    }
}
