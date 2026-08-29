<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use RouterOS\Client;
use RouterOS\Query;

class Xray_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        require_once FCPATH . 'vendor/autoload.php';

        $this->CI->config->load('routeros');
    }

    /**
     * Synchronize one Xray server from the database.
     *
     * Database is the source of truth.
     * Only active users are placed in Xray clients[].
     */
    public function sync_server($server_id)
    {
        $server_id = (int) $server_id;

        if ($server_id <= 0) {
            throw new Exception('Invalid server ID.');
        }

        // Get server
        $server = $this->CI->db
            ->where('id', $server_id)
            ->get('v2ray_servers')
            ->row();

        if (!$server) {
            throw new Exception('Xray server not found.');
        }

        // Get active users
        $users = $this->CI->db
            ->where('server_id', $server_id)
            ->where('status', 'active')
            ->order_by('id', 'ASC')
            ->get('vless_users')
            ->result();

        // Build clients[]
        $clients = array();

        foreach ($users as $user) {

            $client = array(
                'id' => $user->uuid,
                'level' => 0
            );

            // Email is optional
            if ($user->email !== NULL && $user->email !== '') {
                $client['email'] = $user->email;
            }

            $clients[] = $client;
        }

        // Build complete Xray configuration
        $config = array(
            'log' => array(
                'loglevel' => 'info'
            ),

            'inbounds' => array(
                array(
                    'listen' => '0.0.0.0',
                    'port' => 8443,
                    'protocol' => 'vless',

                    'settings' => array(
                        'clients' => $clients,
                        'decryption' => 'none'
                    ),

                    'streamSettings' => array(
                        'network' => 'tcp',
                        'security' => 'tls',

                        'tlsSettings' => array(
                            'certificates' => array(
                                array(
                                    'certificateFile' =>
                                        '/usr/local/etc/xray/server-fullchain.crt',

                                    'keyFile' =>
                                        '/usr/local/etc/xray/server.key'
                                )
                            )
                        )
                    )
                )
            ),

            'outbounds' => array(
                array(
                    'protocol' => 'freedom'
                )
            )
        );

        // Convert to JSON
        $json = json_encode(
            $config,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new Exception(
                'Failed to generate Xray configuration: ' .
                json_last_error_msg()
            );
        }

        // Connect to RouterOS
        $routeros = $this->CI->config->item('routeros');

        $client = new Client(array(
            'host' => $server->host,
            'user' => $routeros['user'],
            'pass' => $routeros['pass'],
            'port' => $routeros['port']
        ));

        /*
         * Temporary file.
         *
         * We never write directly to config.json first.
         */
        $temp_file = 'xray-config/panel8-xray-new.json';

        // Remove old temporary file if it exists
        $query = new Query('/file/print');
        $query->where('name', $temp_file);

        $old_temp = $client
            ->query($query)
            ->read();

        foreach ($old_temp as $row) {
            if (isset($row['.id'])) {

                $remove = new Query('/file/remove');

                $remove->equal(
                    'numbers',
                    $row['.id']
                );

                $client
                    ->query($remove)
                    ->read();
            }
        }

        // Write complete new configuration
        $add = new Query('/file/add');

        $add->equal(
            'name',
            $temp_file
        );

        $add->equal(
            'contents',
            $json
        );

        $client
            ->query($add)
            ->read();

        /*
         * Verify that RouterOS received the file.
         */
        $verify = new Query('/file/print');
        $verify->where('name', $temp_file);

        $result = $client
            ->query($verify)
            ->read();

        if (empty($result)) {
            throw new Exception(
                'Temporary Xray configuration was not created.'
            );
        }

        /*
         * Find the current live configuration.
         */
        $live_query = new Query('/file/print');
        $live_query->where(
            'name',
            'xray-config/config.json'
        );

        $live = $client
            ->query($live_query)
            ->read();

        if (empty($live)) {
            throw new Exception(
                'Live Xray configuration was not found.'
            );
        }

        $live_id = NULL;

        foreach ($live as $row) {
            if (isset($row['.id'])) {
                $live_id = $row['.id'];
                break;
            }
        }

        if ($live_id === NULL) {
            throw new Exception(
                'Could not determine live Xray config ID.'
            );
        }

        /*
         * Rename current config to backup.
         */
        $backup_file = 'xray-config/config.json.bak';

        // Remove old backup if present
        $backup_query = new Query('/file/print');
        $backup_query->where('name', $backup_file);

        $backup_result = $client
            ->query($backup_query)
            ->read();

        foreach ($backup_result as $row) {

            if (isset($row['.id'])) {

                $remove = new Query('/file/remove');

                $remove->equal(
                    'numbers',
                    $row['.id']
                );

                $client
                    ->query($remove)
                    ->read();
            }
        }

        // Rename live → backup
        $rename_backup = new Query('/file/set');

        $rename_backup->equal(
            'numbers',
            $live_id
        );

        $rename_backup->equal(
            'name',
            $backup_file
        );

        $client
            ->query($rename_backup)
            ->read();

        /*
         * Find temporary file ID.
         */
        $temp_query = new Query('/file/print');
        $temp_query->where('name', $temp_file);

        $temp_result = $client
            ->query($temp_query)
            ->read();

        $temp_id = NULL;

        foreach ($temp_result as $row) {

            if (isset($row['.id'])) {
                $temp_id = $row['.id'];
                break;
            }
        }

        if ($temp_id === NULL) {
            throw new Exception(
                'Temporary Xray configuration disappeared.'
            );
        }

        /*
         * Rename temporary → live config.
         */
        $rename_live = new Query('/file/set');

        $rename_live->equal(
            'numbers',
            $temp_id
        );

        $rename_live->equal(
            'name',
            'xray-config/config.json'
        );

        $client
            ->query($rename_live)
            ->read();

        /*
         * Restart Xray container.
         */
        $this->restart_xray($client);

        return array(
            'server_id' => $server_id,
            'users' => count($clients),
            'json' => $json
        );
    }

    /**
     * Restart Xray container.
     */
    protected function restart_xray($client)
    {
        $query = new Query('/container/print');

        $query->where(
            'name',
            'xray'
        );

        $containers = $client
            ->query($query)
            ->read();

        $container_id = NULL;

        foreach ($containers as $container) {

            if (isset($container['.id'])) {
                $container_id = $container['.id'];
                break;
            }
        }

        if ($container_id === NULL) {
            throw new Exception(
                'Xray container was not found.'
            );
        }

        // Stop
        $stop = new Query('/container/stop');

        $stop->equal(
            'numbers',
            $container_id
        );

        $client
            ->query($stop)
            ->read();

        // Start
        $start = new Query('/container/start');

        $start->equal(
            'numbers',
            $container_id
        );

        $client
            ->query($start)
            ->read();
    }
}
