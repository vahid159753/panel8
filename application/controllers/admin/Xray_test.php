<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use RouterOS\Client;
use RouterOS\Query;

class Xray_test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        require_once FCPATH . 'vendor/autoload.php';

        $this->load->database();
    }
    public function restart()
    {
        try {

            $client = new Client([
                'host' => '82.115.25.131',
                'user' => 'panel_api',
                'pass' => 'Delta@2145#',
                'port' => 8728,
            ]);

            echo '<pre>';

            /*
             * Get the only container's API ID
             */
            $query = new Query('/container/print');
            $query->add('=.proplist=.id');

            $raw = $client->query($query)->read(false);

            $containerId = null;

            foreach ($raw as $word) {

                if (strpos($word, '=.id=') === 0) {
                    $containerId = substr($word, 5);
                    break;
                }
            }

            if (!$containerId) {
                throw new Exception('Xray container ID could not be found.');
            }

            echo "Xray API ID: {$containerId}\n";

            /*
             * STOP
             */
            echo "\n=== STOP XRAY ===\n";

            $query = new Query('/container/stop');
            $query->equal('.id', $containerId);

            $response = $client->query($query)->read(false);

            print_r($response);

            sleep(3);

            /*
             * START
             */
            echo "\n=== START XRAY ===\n";

            $query = new Query('/container/start');
            $query->equal('.id', $containerId);

            $response = $client->query($query)->read(false);

            print_r($response);

            echo "\n=== DONE ===\n";

            echo '</pre>';

        } catch (\Throwable $e) {

            echo '<pre>';
            echo "ERROR:\n";
            echo $e->getMessage();
            echo '</pre>';
        }
    }
    public function sync()
    {
        try {
            $this->load->library('Xray_manager');

            // Change 1 to your actual server_id
            $this->xray_manager->sync(1);

            echo 'Xray sync completed successfully.';

        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }
}