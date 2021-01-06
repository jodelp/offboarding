<?php
use Migrations\AbstractMigration;

class CreateSentEmails extends AbstractMigration
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
        $table = $this->table('sent_emails');
        $table
            ->addColumn('description', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
            ])
            ->addColumn('username', 'string', [
                'null' => false,
                'limit' => 50,
            ])
            ->addColumn('client_code', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('email_provider', 'string', [
                'null' => false,
                'limit' => 50,
            ])
            ->addColumn('email_recipient', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
            ])
            ->addColumn('status', 'string', [
                'null' => false,
                'limit' => 50,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true
            ]);
        $table->create();
    }

    /**
     * remove table when when rollback is called
     * @return void
     */
    public function down(): void
    {
        $this->table('sent_emails')->drop()->save();
    }
}
