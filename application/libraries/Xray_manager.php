<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use RouterOS\Client;
use RouterOS\Query;

class Xray_manager
{
    protected $CI;

    protected $router_host = '82.115.25.131';
    protected $router_user = 'panel_api';
    protected $router_pass = 'Delta@2145#';
    protected $router_port = 8728;

    protected $config_file = 'xray-config/config.json';

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->database();
    }

    /**
     * Generate complete Xray configuration for a server.
     */
    public function generate_config($server_id)
    {
        $server = $this->CI->db
            ->where('id', (int) $server_id)
            ->where('status', 'active')
            ->get('v2ray_servers')
            ->row();

        if (!$server) {
            throw new Exception('Xray server not found or disabled.');
        }

        $users = $this->CI->db
            ->where('server_id', (int) $server_id)
            ->where('status', 'active')
            ->group_start()
            ->where('expires_at IS NULL', NULL, FALSE)
            ->or_where('expires_at >', date('Y-m-d H:i:s'))
            ->group_end()
            ->get('vless_users')
            ->result();

        $clients = array();

        foreach ($users as $user) {

            $client = array(
                'id' => $user->uuid,
                'level' => 0
            );

            if (!empty($user->email)) {
                $client['email'] = $user->email;
            }

            $clients[] = $client;
        }

        $config = array(
            'log' => array(
                'loglevel' => 'info'
            ),

            'inbounds' => array(
                array(
                    'listen' => '0.0.0.0',
                    //'port' => (int) $server->port,
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

        return json_encode(
            $config,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Connect to MikroTik RouterOS API.
     */
    protected function connect()
    {
        $config = array(
            'host' => $this->router_host,
            'user' => $this->router_user,
            'pass' => $this->router_pass,
            'port' => $this->router_port
        );

        return new Client($config);
    }

    public function sync($server_id)
    {
        $json = $this->generate_config($server_id);

        $client = $this->connect();

        /*
         * Write the complete Xray configuration.
         */
        $this->write_file(
            $client,
            $this->config_file,
            $json
        );

        /*
         * Restart Xray so the new configuration becomes active.
         */
        $this->restart($client);

        return true;
    }

    /**
     * Write a complete file to RouterOS.
     */
    protected function write_file($client, $filename, $contents)
    {
        /*
         * First create/truncate the file.
         */
        $query = new Query('/file/add');

        $query->equal(
            'name',
            $filename
        );

        $client->query($query)->read();

        /*
         * RouterOS /file/set can write the contents.
         */
        $query = new Query('/file/set');

        $query->equal(
            'numbers',
            $filename
        );

        $query->equal(
            'contents',
            $contents
        );

        $response = $client
            ->query($query)
            ->read();

        return $response;
    }
    protected function restart($client)
    {
        /*
         * There is currently only one container on MikroTik,
         * which is Xray.
         *
         * Get its dynamic RouterOS API ID.
         */
        $query = new Query('/container/print');

        $query->add('=.proplist=.id');

        $raw = $client
            ->query($query)
            ->read(false);

        $containerId = null;

        foreach ($raw as $word) {

            if (strpos($word, '=.id=') === 0) {
                $containerId = substr($word, 5);
                break;
            }
        }

        if (!$containerId) {
            throw new Exception(
                'Xray container ID could not be found.'
            );
        }

        /*
         * Stop Xray.
         */
        $query = new Query('/container/stop');

        $query->equal(
            '.id',
            $containerId
        );

        $client
            ->query($query)
            ->read(false);

        /*
         * Give Xray time to stop completely.
         */
        sleep(2);

        /*
         * Start Xray.
         */
        $query = new Query('/container/start');

        $query->equal(
            '.id',
            $containerId
        );

        $client
            ->query($query)
            ->read(false);

        return true;
    }
}