<?php
include("DBHelper.php");
class BookController {
    public function __construct() {}

    /**
     * Function to obtain all registered users
    */
    public function getUsers() {//login 沒打username
        $dbLink = new DBHelper();
        $query = "SELECT u.id, u.title, u.author, u.isbn, u.abstract, u.publisher, u.publication_year "
        . " FROM books u ";
        $users = array();

        try {
            // Create connection to DB
            $dbLink->connect();
            // Set the prepared statement for the query
            $stmt = $dbLink->getConnection()->prepare($query);
            // Since there is no parameters need to be prepared at this moment, directly execute the prepared statement
            $stmt->execute();
            // Bind and store the query result
            $rs = $stmt->get_result();
            // Iterate through the result set
            while ($row=$rs->fetch_assoc()) {
                $user = array($row['id'], $row['title'], $row['author'], $row['isbn'], $row['abstract'], $row['publisher'], $row['publication_year']);

                // Push the retrieved user data to the users array
                array_push($users, $user);
            }
        } catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false); // Close the statement to free up memory
            (isset($dbLink)? $dbLink->close() : false); // Close the database connection
        }
        return $users;
    }

    public function getUser($username) {//http://localhost/api/UserAPI.php/users?username=alice ////login 有打username
        $dbLink = new DBHelper();
		$query = "SELECT * FROM `books` where `title` = ?";//我們要查詢的資料變數，是以「?」來代替。
        $user = null;

        try {
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($query); // Set the prepared statement to process the query
            $c_username = $dbLink->cleanInput($username); // Sanitize the variables before sending it to the server
            $stmt->bind_param("s", $c_username); // Bind the query's parameters
			//用bind_param，去將我們的變數，與「?」做結合，而「”s”」，代表的是string
            $stmt->execute(); // Execute the prepared statement
            $rs = $stmt->get_result(); // Bind and store the query result
            while ($row=$rs->fetch_assoc()) { // Iterate through the result set
                $user = array($row['id'], $row['title'], $row['author'], $row['isbn'], $row['abstract'], $row['publisher'], $row['publication_year']);
                break;
            }
        } catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false);
            (isset($dbLink)? $dbLink->close() : false);
        }
        return (is_null($user)? null : $user);
    }
	
	public function putUser($title, $author, $isbn, $abstract, $publisher, $publication_year) {//registration新增user
		$dbLink = new DBHelper();
		$created_date=date("Y-m-d H:i:s");
		//檢查username是否重複
		$checkuid_sql = "SELECT `title` FROM `books` where `title` = ?";
		//更改
        $query = "UPDATE `books` SET `author`='$author',`isbn`= '$isbn',`abstract`= '$abstract', `publisher`= '$publisher' , `publication_year`= '$publication_year', `last_modified_date`= '$created_date' WHERE `title` = ?";
		//得到新增後的資料(id)
		$ask_sql = "SELECT `id`, `title`, `author` , `last_modified_date` FROM `books` where `title` = ?";
        $user = null;
        try {
			//檢查username是否已存在
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($checkuid_sql); 
            $c_username = $dbLink->cleanInput($title);
            $stmt->bind_param("s", $c_username);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
            $add_user = null;
			if ( $row ==""){ //若重複
				echo "The book does not exist!<br/>\n<br/>\n";
			}else {
				$stmt = $dbLink->getConnection()->prepare($query);
				$title = $dbLink->cleanInput($title);				
				$isbn = $dbLink->cleanInput($isbn);
				$abstract = $dbLink->cleanInput($abstract);
				$publisher = $dbLink->cleanInput($publisher);
				$publication_year = $dbLink->cleanInput($publication_year);
				$created_date = $dbLink->cleanInput($created_date);
				$stmt->bind_param("s", $title);
				$stmt->execute();
				
				$stmt = $dbLink->getConnection()->prepare($ask_sql);
				$title = $dbLink->cleanInput($title);				
				$isbn = $dbLink->cleanInput($isbn);
				$abstract = $dbLink->cleanInput($abstract);
				$publisher = $dbLink->cleanInput($publisher);
				$publication_year = $dbLink->cleanInput($publication_year);
				$created_date = $dbLink->cleanInput($created_date);
				$stmt->bind_param("s", $title);				
				$stmt->execute();
				$rs = $stmt->get_result();
				while ($row=$rs->fetch_assoc()) { 
					 $add_user = array($row['id'], $row['title'], $row['author'], $row['last_modified_date']);
					break;
				}
			}
		} catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false);
            (isset($dbLink)? $dbLink->close() : false);
        }
        return ($add_user);
    }
	public function postUser($title, $author, $isbn, $abstract, $publisher, $publication_year) {//registration新增user
        $dbLink = new DBHelper();
		$created_date=date("Y-m-d H:i:s");
		//檢查username是否重複
		$checkuid_sql = "SELECT `title` FROM `books` where `title` = ?";
		//新增
        $query = "INSERT INTO `books` (`title`, `author`, `isbn`, `abstract`, `publisher`, `publication_year`, `created_date`)VALUES ('$title', '$author', '$isbn', '$abstract', '$publisher', '$publication_year', '$created_date')";
		//得到新增後的資料(id)
		$ask_sql = "SELECT `id`, `title`, `author` FROM `books` where `title` = ?";
        $user = null;
        try {
			//檢查username是否已存在
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($checkuid_sql); 
            $c_username = $dbLink->cleanInput($title);
            $stmt->bind_param("s", $c_username);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
            $add_user = null;
			if ( $row !=""){ //若重複
				echo "The book is existing!<br/>\n<br/>\n";
			}else {
				$stmt = $dbLink->getConnection()->prepare($query);
				$title = $dbLink->cleanInput($title);				
				$isbn = $dbLink->cleanInput($isbn);
				$abstract = $dbLink->cleanInput($abstract);
				$publisher = $dbLink->cleanInput($publisher);
				$publication_year = $dbLink->cleanInput($publication_year);
				$created_date = $dbLink->cleanInput($created_date);
				$stmt->execute();
				
				$stmt = $dbLink->getConnection()->prepare($ask_sql);
				$title = $dbLink->cleanInput($title);				
				$isbn = $dbLink->cleanInput($isbn);
				$abstract = $dbLink->cleanInput($abstract);
				$publisher = $dbLink->cleanInput($publisher);
				$publication_year = $dbLink->cleanInput($publication_year);
				$created_date = $dbLink->cleanInput($created_date);
				$stmt->bind_param("s", $title);				
				$stmt->execute();
				$rs = $stmt->get_result();
				while ($row=$rs->fetch_assoc()) { 
					 $add_user = array($row['id'], $row['title'], $row['author']);
					break;
				}
			}
		} catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false);
            (isset($dbLink)? $dbLink->close() : false);
        }
        return ($add_user);
    }
	
	public function deleteUser($username) {//http://localhost/api/UserAPI.php/users?username=alice ////login 有打username
        $dbLink = new DBHelper();
		$query = "DELETE FROM `books` where `title` = ?";//我們要查詢的資料變數，是以「?」來代替。
		$check_query = "SELECT `title` FROM `books` where `title` = ?";
        $user = null;

        try {
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($query); // Set the prepared statement to process the query
            $c_username = $dbLink->cleanInput($username); // Sanitize the variables before sending it to the server
            $stmt->bind_param("s", $c_username); // Bind the query's parameters
			//用bind_param，去將我們的變數，與「?」做結合，而「”s”」，代表的是string
            $stmt->execute(); // Execute the prepared statement
            $rs = $stmt->get_result(); // Bind and store the query result
			
			$stmt = $dbLink->getConnection()->prepare($check_query);
            $c_username = $dbLink->cleanInput($username);
            $stmt->bind_param("s", $c_username);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
			if ( $row !=""){ //若重複
				$success=0;
			}
			else{
				$success=1;
			}
        } catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false);
            (isset($dbLink)? $dbLink->close() : false);
        }
        return ($success);
    }
}
?>