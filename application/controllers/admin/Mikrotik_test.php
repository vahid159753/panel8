```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once FCPATH . 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

class Mikrotik_test extends CI_Controller
{
    public function index()
    {
        try {
            $client = new Client([
                'host' => '82.115.25.131',
                'user' => 'panel_api',
                'pass' => 'Delta@2145#',
                'port' => 8728,
                'timeout' => 5,
            ]);

            $query = new Query('/system/resource/print');

            $response = $client->query($query)->read();

            echo '<pre>';
            print_r($response);
            echo '</pre>';

        } catch (\Throwable $e) {

            echo '<pre>';
            echo 'RouterOS API ERROR:' . PHP_EOL;
            echo $e->getMessage();
            echo '</pre>';
        }
    }
}
