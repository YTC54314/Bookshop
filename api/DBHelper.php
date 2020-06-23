<?php
/**
 * Class to manage Database connection and settings
*/
class DBHelper {
    /**
     * Property: conn
     * The link that store the connection information and could be used to open or close connection channel
    */
    private $conn;

    public function __construct() {}

    /**
     * Establish a new database connection
     * @return database connection handler
    */
	public function connect($db_host="140.118.110.32", $db_username="idsl_test", $db_password="idsl_web_service", $db_name="idsl_web_service", $db_port="53306") {
	
	// public function connect($db_host="localhost", $db_username="m10609304", $db_password="2arxjjlx", $db_name="m10609304"){
		// $this->conn = new mysqli($db_host, $db_username, $db_password, $db_name);
		// mysqli_connect($db_host,$db_user,$db_pass,$db_database,$db_port);
        $this->conn = mysqli_connect($db_host, $db_username, $db_password, $db_name, $db_port);
		
        if (!$this->conn) {
            $strMessage = 'Unable to connect to MySQL Server' . PHP_EOL
                . 'Debugging errno: ' . mysqli_connect_errno() . PHP_EOL
                . 'Debugging error: ' . mysqli_connect_error() . PHP_EOL;
            echo json_encode(array("status"=>"error", "message"=>$strMessage));
            exit;
        }

        return $this->conn;
    }

    public function close() {
        if (!is_null($this->conn) && !$this->conn->close()) {
            // Close the database connection if it still opens
            return false;
        }
        return true;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function cleanInput($varInput) {
        // Ensure that the parameters or arguments send as database query is “clean”
        if (is_array($varInput)) {
            foreach ($varInput as $key=>$value) {
                $result[$key] = $this->cleanInput($value);
            }
        }
        else {
            if (!empty($varInput)) {
                $result = strip_tags(trim($varInput));
                // $result = $this->conn->real_escape_string($varInput);
            } else {
                $result = "";
            }
        }
        return $result;
    }
}
?>
