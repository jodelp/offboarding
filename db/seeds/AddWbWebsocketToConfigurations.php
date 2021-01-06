<?php


use Phinx\Seed\AbstractSeed;

class AddWbWebsocketToConfigurations extends AbstractSeed
{
    /**
     * Adding the wb-websocket-url ti our configurations table
     *
     * To run the script execute below:
     *
     * php vendor/bin/phinx seed:run -s AddWbWebsocketToConfigurations
     */
    public function run()
    {
        /**
         * Staging Url
         * https://stage-wbsocket.cloudstaff.com/
         *
         * Production Url
         * https://wbsocket.cloudstaff.com/
         */
        $data = [
            [
                'name' => 'wb-websocket-url',
                'value' => ''
            ]
        ];

        $table = $this->table('configurations');
        $table->insert($data)->saveData();
    }
}
