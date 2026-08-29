<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vless_users_model extends CI_Model
{
    protected $table = 'vless_users';

    public function get_all()
    {
        return $this->db
            ->select('vless_users.*, v2ray_servers.name AS server_name')
            ->from($this->table)
            ->join(
                'v2ray_servers',
                'v2ray_servers.id = vless_users.server_id',
                'left'
            )
            ->order_by('vless_users.id', 'DESC')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }
    public function mark_expired()
    {
        return $this->db
            ->where('status', 'active')
            ->where('expires_at IS NOT NULL', NULL, FALSE)
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->update(
                $this->table,
                array(
                    'status' => 'expired',
                    'updated_at' => date('Y-m-d H:i:s')
                )
            );
    }
}