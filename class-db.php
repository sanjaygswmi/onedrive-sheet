<?php
class DB {
    private $dbHost     = "DB_HOST";
    private $dbUsername = "DB_USERNAME";
    private $dbPassword = "DB_PASSWORD";
    private $dbName     = "DB_NAME";
 
    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if($conn->connect_error){
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }else{
                $this->db = $conn;
            }
        }
    }
 
    public function is_table_empty() {
        $result = $this->db->query("SELECT id FROM onedrive_oauth WHERE provider = 'onedrive'");
        if($result->num_rows) {
            return false;
        }
 
        return true;
    }
 
    public function get_access_token() {
        $sql = $this->db->query("SELECT provider_value FROM onedrive_oauth WHERE provider = 'onedrive'");
        $result = $sql->fetch_assoc();
        return json_decode($result['provider_value']);
    }
 
    public function get_refersh_token() {
        $result = $this->get_access_token();
        return $result->refresh_token;
    }
 
    public function update_access_token($token) {
        if($this->is_table_empty()) {
            $this->db->query("INSERT INTO onedrive_oauth(provider, provider_value) VALUES('onedrive', '$token')");
        } else {
            $this->db->query("UPDATE onedrive_oauth SET provider_value = '$token' WHERE provider = 'onedrive'");
        }
    }
}