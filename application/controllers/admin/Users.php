<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('session');
        $this->load->database();
        $this->load->model('Vless_users_model');
    }

    public function index()
    {
        $data['users'] =
            $this->Vless_users_model->get_all();

        $data['success'] =
            $this->session->flashdata('success');

        $data['error'] =
            $this->session->flashdata('error');

        $this->load->view(
            'admin/users/index',
            $data
        );
    }

    public function create()
    {
        $data['servers'] = $this->db
            ->where('status', 'active')
            ->order_by('name', 'ASC')
            ->get('v2ray_servers')
            ->result();

        $data['form'] = array();

        // Show form
        if ($this->input->method() !== 'post') {
            $this->load->view('admin/users/create', $data);
            return;
        }

        // Get submitted values
        $username = trim($this->input->post('username', TRUE));
        $server_id = (int) $this->input->post('server_id');
        $email = trim($this->input->post('email', TRUE));
        $traffic_limit_gb = $this->input->post('traffic_limit_gb');
        $duration_days = $this->input->post('duration_days');

        // Keep submitted values if validation fails
        $data['form'] = array(
            'username' => $username,
            'server_id' => $server_id,
            'email' => $email,
            'traffic_limit_gb' => $traffic_limit_gb,
            'duration_days' => $duration_days
        );

        // Basic validation
        if ($username === '') {
            $data['error'] = 'Username is required.';

            $this->load->view('admin/users/create', $data);
            return;
        }

        if ($server_id <= 0) {
            $data['error'] = 'Please select a server.';

            $this->load->view('admin/users/create', $data);
            return;
        }

        // Verify server exists and is active
        $server = $this->db
            ->where('id', $server_id)
            ->where('status', 'active')
            ->get('v2ray_servers')
            ->row();

        if (!$server) {
            $data['error'] = 'Selected server is not available.';

            $this->load->view('admin/users/create', $data);
            return;
        }

        // Check duplicate username on the same server
        $existing = $this->db
            ->where('server_id', $server_id)
            ->where('username', $username)
            ->get('vless_users')
            ->row();

        if ($existing) {
            $data['error'] = 'This username already exists on this server.';

            $this->load->view('admin/users/create', $data);
            return;
        }

        /*
         * Generate UUID
         *
         * We use PHP's random_bytes() instead of depending on
         * the Xray binary being available on the MikroTik.
         */
        $uuid = $this->generate_uuid();

        // Traffic limit
        $traffic_limit_bytes = NULL;

        if (
            $traffic_limit_gb !== ''
            && is_numeric($traffic_limit_gb)
        ) {
            $traffic_limit_gb = (float) $traffic_limit_gb;

            if ($traffic_limit_gb < 0) {
                $data['error'] = 'Traffic limit cannot be negative.';

                $this->load->view('admin/users/create', $data);
                return;
            }

            $traffic_limit_bytes = (int) round(
                $traffic_limit_gb * 1073741824
            );
        }

        // Expiration
        $expires_at = NULL;

        if (
            $duration_days !== ''
            && is_numeric($duration_days)
        ) {
            $duration_days = (int) $duration_days;

            if ($duration_days < 1) {
                $data['error'] = 'Duration must be at least 1 day.';

                $this->load->view('admin/users/create', $data);
                return;
            }

            $expires_at = date(
                'Y-m-d H:i:s',
                strtotime('+' . $duration_days . ' days')
            );
        }

        // Insert user
        $user_data = array(
            'server_id' => $server_id,
            'username' => $username,
            'email' => $email !== '' ? $email : NULL,
            'uuid' => $uuid,
            'status' => 'active',
            'traffic_limit_bytes' => $traffic_limit_bytes,
            'traffic_used_bytes' => 0,
            'expires_at' => $expires_at,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => NULL
        );

        $user_id = $this->Vless_users_model->insert($user_data);

        if (!$user_id) {
            $data['error'] = 'Failed to create user.';

            $this->load->view('admin/users/create', $data);
            return;
        }

        redirect('admin/users');
    }
    public function edit($id)
    {
        $id = (int) $id;

        $user = $this->Vless_users_model->get($id);

        if (!$user) {
            show_404();
            return;
        }

        // Get active servers
        $data['servers'] = $this->db
            ->where('status', 'active')
            ->order_by('name', 'ASC')
            ->get('v2ray_servers')
            ->result();

        // Show form
        if ($this->input->method() !== 'post') {

            $data['user'] = $user;

            $data['form'] = array(
                'username' => $user->username,
                'email' => $user->email,
                'server_id' => $user->server_id,
                'traffic_limit_gb' => $user->traffic_limit_bytes !== null
                    ? $user->traffic_limit_bytes / 1073741824
                    : '',
                'duration_days' => $user->expires_at
                    ? max(
                        1,
                        ceil(
                            (strtotime($user->expires_at) - time()) / 86400
                        )
                    )
                    : '',
                'status' => $user->status
            );

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Get submitted values
        $username = trim(
            $this->input->post('username', TRUE)
        );

        $email = trim(
            $this->input->post('email', TRUE)
        );

        $server_id = (int) $this->input->post('server_id');

        $traffic_limit_gb =
            $this->input->post('traffic_limit_gb');

        $duration_days =
            $this->input->post('duration_days');

        $status =
            $this->input->post('status', TRUE);

        // Keep submitted values if validation fails
        $data['user'] = $user;

        $data['form'] = array(
            'username' => $username,
            'email' => $email,
            'server_id' => $server_id,
            'traffic_limit_gb' => $traffic_limit_gb,
            'duration_days' => $duration_days,
            'status' => $status
        );

        // Username required
        if ($username === '') {

            $data['error'] = 'Username is required.';

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Server required
        if ($server_id <= 0) {

            $data['error'] = 'Please select a server.';

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Valid status
        if (!in_array(
            $status,
            array('active', 'disabled', 'expired'),
            TRUE
        )) {

            $data['error'] = 'Invalid status.';

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Verify server
        $server = $this->db
            ->where('id', $server_id)
            ->get('v2ray_servers')
            ->row();

        if (!$server) {

            $data['error'] = 'Selected server does not exist.';

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Check duplicate username
        $existing = $this->db
            ->where('server_id', $server_id)
            ->where('username', $username)
            ->where('id !=', $id)
            ->get('vless_users')
            ->row();

        if ($existing) {

            $data['error'] =
                'This username already exists on this server.';

            $this->load->view(
                'admin/users/edit',
                $data
            );

            return;
        }

        // Traffic limit
        $traffic_limit_bytes = NULL;

        if (
            $traffic_limit_gb !== ''
            && is_numeric($traffic_limit_gb)
        ) {

            $traffic_limit_gb =
                (float) $traffic_limit_gb;

            if ($traffic_limit_gb < 0) {

                $data['error'] =
                    'Traffic limit cannot be negative.';

                $this->load->view(
                    'admin/users/edit',
                    $data
                );

                return;
            }

            $traffic_limit_bytes = (int) round(
                $traffic_limit_gb * 1073741824
            );
        }

        // Expiration
        $expires_at = NULL;

        if (
            $duration_days !== ''
            && is_numeric($duration_days)
        ) {

            $duration_days =
                (int) $duration_days;

            if ($duration_days < 1) {

                $data['error'] =
                    'Duration must be at least 1 day.';

                $this->load->view(
                    'admin/users/edit',
                    $data
                );

                return;
            }

            $expires_at = date(
                'Y-m-d H:i:s',
                strtotime(
                    '+' . $duration_days . ' days'
                )
            );
        }

        // Update user
        $update_data = array(
            'server_id' => $server_id,
            'username' => $username,
            'email' => $email !== '' ? $email : NULL,
            'status' => $status,
            'traffic_limit_bytes' => $traffic_limit_bytes,
            'expires_at' => $expires_at,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->Vless_users_model->update(
            $id,
            $update_data
        );

        redirect('admin/users');
    }
    public function toggle($id)
    {
        $id = (int) $id;

        $user = $this->Vless_users_model->get($id);

        if (!$user) {
            show_404();
            return;
        }

        // Only active and disabled users can be toggled.
        if ($user->status === 'active') {
            $new_status = 'disabled';
        } elseif ($user->status === 'disabled') {
            $new_status = 'active';
        } else {
            // Expired users cannot be re-enabled from here.
            $this->session->set_flashdata(
                'error',
                'An expired user cannot be enabled this way.'
            );

            redirect('admin/users');
            return;
        }

        $this->Vless_users_model->update(
            $id,
            array(
                'status' => $new_status,
                'updated_at' => date('Y-m-d H:i:s')
            )
        );

        $this->session->set_flashdata(
            'success',
            'User status changed to ' . $new_status . '.'
        );

        redirect('admin/users');
    }
    private function generate_uuid()
    {
        $data = random_bytes(16);

        $data[6] = chr(
            ord($data[6]) & 0x0f | 0x40
        );

        $data[8] = chr(
            ord($data[8]) & 0x3f | 0x80
        );

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }
}