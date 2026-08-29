
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use RouterOS\Client;
use RouterOS\Query;

class Mikrotik_test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        require_once FCPATH . 'vendor/autoload.php';
    }

    public function index()
    {
        try {

            $client = new Client([
                'host' => '82.115.25.131',
                'user' => 'panel_api',
                'pass' => 'Delta@2145#',
                'port' => 8728,
            ]);

            $config = [
                'log' => [
                    'loglevel' => 'info'
                ],
                'inbounds' => [
                    [
                        'listen' => '0.0.0.0',
                        'port' => 8443,
                        'protocol' => 'vless',
                        'settings' => [
                            'clients' => [
                                [
                                    'id' => '8d1c4ed0-2794-4556-a9db-cb5b5a212caf',
                                    'level' => 0,
                                    'email' => 'client1'
                                ]
                            ],
                            'decryption' => 'none'
                        ],
                        'streamSettings' => [
                            'network' => 'tcp',
                            'security' => 'tls',
                            'tlsSettings' => [
                                'certificates' => [
                                    [
                                        'certificateFile' => '/usr/local/etc/xray/server-fullchain.crt',
                                        'keyFile' => '/usr/local/etc/xray/server.key'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'outbounds' => [
                    [
                        'protocol' => 'freedom'
                    ]
                ]
            ];

            $json = json_encode(
                $config,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );

            if ($json === false) {
                throw new Exception('Failed to encode Xray JSON.');
            }

            $query = new Query('/file/add');

            $query->equal(
                'name',
                'xray-config/panel8-xray-test.json'
            );

            $query->equal(
                'contents',
                $json
            );

            $response = $client->query($query)->read();

            echo '<pre>';
            print_r($response);
            echo "\n\nGenerated JSON:\n";
            echo htmlspecialchars($json);
            echo '</pre>';

        } catch (Throwable $e) {

            echo '<pre>';
            echo "RouterOS API ERROR:\n";
            echo htmlspecialchars($e->getMessage());
            echo '</pre>';
        }
    }
    public function xray_sync_test()
    {
        try {

            $this->load->library('Xray_manager');

            $json = $this->xray_manager->generate_config(1);

            echo '<pre>';
            echo htmlspecialchars($json);
            echo '</pre>';

        } catch (Exception $e) {

            echo '<pre>';
            echo 'XRAY MANAGER ERROR: ';
            echo $e->getMessage();
            echo '</pre>';
        }
    }



}

