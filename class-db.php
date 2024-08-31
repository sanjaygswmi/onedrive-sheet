<?php

class DB {
    
    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if($conn->connect_error){
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }else{
                $this->db = $conn;
            }

            $this->setup_table();
        }
    }

    public function setup_table() {
        $sql = 'CREATE TABLE IF NOT EXISTS `oauth_token` ( `id` int(11) NOT NULL AUTO_INCREMENT, `provider` varchar(255) NOT NULL, `provider_value` text NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

        if ($this->db->query($sql) === TRUE) {
            // echo "Table created successfully";
        } else {
            // echo "Error creating table: " . $conn->error;
        }
    }
 
    public function is_table_empty() {
        $result = $this->db->query("SELECT id FROM `oauth_token` WHERE provider = 'onedrive'");
        if($result->num_rows) {
            return false;
        }
 
        return true;
    }
 
    public function get_access_token() {
        $sql = $this->db->query("SELECT provider_value FROM `oauth_token` WHERE provider = 'onedrive'");
        $result = $sql->fetch_assoc();
        return json_decode($result['provider_value']);
    }
 
    public function get_refersh_token() {
        $result = $this->get_access_token();
        return $result->refresh_token;
    }
 
    public function update_access_token($token) {
        if($this->is_table_empty()) {
            $this->db->query("INSERT INTO `oauth_token`(provider, provider_value) VALUES('onedrive', '$token')");
        } else {
            $this->db->query("UPDATE `oauth_token` SET provider_value = '$token' WHERE provider = 'onedrive'");
        }
    }
}