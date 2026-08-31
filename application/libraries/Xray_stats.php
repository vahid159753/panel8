<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Grpc\ChannelCredentials;
use Xray\App\Stats\Command\StatsServiceClient;
use Xray\App\Stats\Command\GetUsersStatsRequest;

class Xray_stats
{
    protected $host = '82.115.25.131:10085';

    protected $client;

    public function __construct()
    {
        $this->client = new StatsServiceClient(
            $this->host,
            array(
                'credentials' => ChannelCredentials::createInsecure()
            )
        );
    }

    public function get_users_stats()
    {
        $request = new GetUsersStatsRequest();

        $request->setIncludeTraffic(true);
        $request->setReset(false);

        list($response, $status) =
            $this->client->GetUsersStats(
                $request
            )->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new Exception(
                'Xray API error: ' . $status->details
            );
        }

        return $response;
    }
}