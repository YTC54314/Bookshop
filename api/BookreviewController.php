<?php
include("DBHelper.php");
class BookreviewController {
    public function __construct() {}

    /**
     * Function to obtain all registered users
    */
    public function getUsers_nouser($bookname) {
        $dbLink = new DBHelper();
        $query = "SELECT `user_id`, `book_id`, `rating`, `review` FROM `book_reviews` where `book_id`=?";
        $users = array();
        try {
            $dbLink->connect();
            $stmt = $dbLink->getConnection()->prepare($query);
			$book_id = $dbLink->cleanInput($bookname);
			$stmt->bind_param('s', $book_id);
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($row=$rs->fetch_assoc()) {
                $user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review']);
                array_push($users, $user);
            }
        } catch (Exception $e) {
        } finally {
            (isset($stmt)? $stmt->close() : false); // Close the statement to free up memory
            (isset($dbLink)? $dbLink->close() : false); // Close the database connection
        }
        return $users;
    }
	
	public function getUsers_nobook($username) {
            $dbLink = new DBHelper();
        $query = "SELECT `user_id`, `book_id`, `rating`, `review` FROM `book_reviews` where `user_id`=?";
        $users = array();
        try {
            $dbLink->connect();
            $stmt = $dbLink->getConnection()->prepare($query);
			$user_id = $dbLink->cleanInput($username);
			$stmt->bind_param('s', $user_id);
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($row=$rs->fetch_assoc()) {
                $user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review']);
                array_push($users, $user);
            }
        } catch (Exception $e) {
        } finally {
            (isset($stmt)? $stmt->close() : false); // Close the statement to free up memory
            (isset($dbLink)? $dbLink->close() : false); // Close the database connection
        }
        return $users;
    }
	
	
	public function getUser_both() {//皆空
		//http://localhost/api/BookreviewAPI.php/books/
        $dbLink = new DBHelper();
        $users = array();
		$query = "SELECT `user_id`, `book_id`, `rating`, `review` FROM `book_reviews`";
        try {
            $dbLink->connect();
            $stmt = $dbLink->getConnection()->prepare($query);
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($row=$rs->fetch_assoc()) {
                $user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review']);
                array_push($users, $user);
            }
        } catch (Exception $e) {
            // echo json_encode(array("status"=>"error", "message"=>$e->getMessage()));
        } finally {
            (isset($stmt)? $stmt->close() : false);
            (isset($dbLink)? $dbLink->close() : false);
        }
		// print_r($users);
        return (is_null($users)? null : $users);
    }

    public function getUser($username, $bookname) {//都有打
		//http://localhost/api/BookreviewAPI.php/books?ueser_id=100&book_id=36
        $dbLink = new DBHelper();
        $user = null;
		$query = "SELECT `user_id`, `book_id`, `rating`, `review` FROM `book_reviews` where `user_id`=? and `book_id`=?";
        try {
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($query); // Set the prepared statement to process the query
            $user_id = $dbLink->cleanInput($username); // Sanitize the variables before sending it to the server
            $book_id = $dbLink->cleanInput($bookname); // Sanitize the variables before sending it to the server
            $stmt->bind_param('ss', $user_id, $book_id);
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($row=$rs->fetch_assoc()) {
                $user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review']);
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
	
	public function putUser($user_id, $book_id, $rating, $review) {//registration新增user
        $dbLink = new DBHelper();
		$created_date=date("Y-m-d H:i:s");
		//檢查username是否重複
		$checkuid_sql = "SELECT `book_id` FROM `book_reviews` where `user_id`=? and `book_id` = ?";
		//新增
        $query = "UPDATE `book_reviews` SET `rating`='$rating',`review`= '$review',`last_modified_date`= '$created_date' where `user_id`=? and `book_id` = ?";
		//得到新增後的資料(id)
		$ask_sql = "SELECT `user_id`, `book_id`, `rating`, `review`, `last_modified_date` FROM `book_reviews` where `user_id`=? and `book_id` = ?";
        $user = null;
        try {
			//檢查username是否已存在
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($checkuid_sql); 
            $user_id = $dbLink->cleanInput($user_id);
            $book_id = $dbLink->cleanInput($book_id);
            $stmt->bind_param("ss", $user_id,$book_id);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
            $add_user = null;
			if ( $row ==""){ //若重複
				echo "The review of this book is not yet created!<br/>\n<br/>\n";
			}else {
				$stmt = $dbLink->getConnection()->prepare($query);
				$user_id = $dbLink->cleanInput($user_id);				
				$book_id = $dbLink->cleanInput($book_id);
				$rating = $dbLink->cleanInput($rating);
				$review = $dbLink->cleanInput($review);
				$created_date = $dbLink->cleanInput($created_date);
				$user_id = $dbLink->cleanInput($user_id);
				$book_id = $dbLink->cleanInput($book_id);
				$stmt->bind_param("ss", $user_id,$book_id);
				$stmt->execute();
				
				$stmt = $dbLink->getConnection()->prepare($ask_sql);
				$user_id = $dbLink->cleanInput($user_id);				
				$book_id = $dbLink->cleanInput($book_id);
				$rating = $dbLink->cleanInput($rating);
				$review = $dbLink->cleanInput($review);
				$created_date = $dbLink->cleanInput($created_date);
                $stmt->bind_param("ss", $user_id,$book_id);
				$stmt->execute();
				$rs = $stmt->get_result();
				while ($row=$rs->fetch_assoc()) { 
					 $add_user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review'], $row['last_modified_date']);
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
	public function postUser($user_id, $book_id, $rating, $review) {//registration新增user
        $dbLink = new DBHelper();
		$created_date=date("Y-m-d H:i:s");
		//檢查username是否重複
		$checkuid_sql = "SELECT `book_id` FROM `book_reviews` where `user_id`=? and `book_id` = ?";
		//新增
        $query = "INSERT INTO `book_reviews` (`user_id`, `book_id`, `rating`, `review`, `review_date`, `last_modified_date`, `created_date`)VALUES ('$user_id', '$book_id', '$rating', '$review', '$created_date', '$created_date', '$created_date')";
		//得到新增後的資料(id)
		$ask_sql = "SELECT `user_id`, `book_id`, `rating`, `review`, `review_date` FROM `book_reviews` where `user_id`=? and `book_id` = ?";
        $user = null;
        try {
			//檢查username是否已存在
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($checkuid_sql); 
            $user_id = $dbLink->cleanInput($user_id);
            $book_id = $dbLink->cleanInput($book_id);
            $stmt->bind_param("ss", $user_id,$book_id);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
            $add_user = null;
			if ( $row !=""){ //若重複
				echo "The book is existing!<br/>\n<br/>\n";
			}else {
				$stmt = $dbLink->getConnection()->prepare($query);
				$user_id = $dbLink->cleanInput($user_id);				
				$book_id = $dbLink->cleanInput($book_id);
				$rating = $dbLink->cleanInput($rating);
				$review = $dbLink->cleanInput($review);
				$created_date = $dbLink->cleanInput($created_date);
				$stmt->execute();
				
				$stmt = $dbLink->getConnection()->prepare($ask_sql);
				$user_id = $dbLink->cleanInput($user_id);				
				$book_id = $dbLink->cleanInput($book_id);
				$rating = $dbLink->cleanInput($rating);
				$review = $dbLink->cleanInput($review);
				$created_date = $dbLink->cleanInput($created_date);
                $stmt->bind_param("ss", $user_id,$book_id);
				$stmt->execute();
				$rs = $stmt->get_result();
				while ($row=$rs->fetch_assoc()) { 
					 $add_user = array($row['user_id'], $row['book_id'], $row['rating'], $row['review'], $row['review_date']);
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
	
	public function deleteUser($user_id, $book_id) {//http://localhost/api/UserAPI.php/users?username=alice ////login 有打username
        $dbLink = new DBHelper();
		$query = "DELETE FROM `book_reviews` where `user_id` = ? and `book_id`=?";//我們要查詢的資料變數，是以「?」來代替。
		$check_query = "SELECT `book_id` FROM `book_reviews` where `user_id`=? and `book_id` = ?";
        $user = null;

        try {
            $dbLink->connect(); // Create a connection to database
            $stmt = $dbLink->getConnection()->prepare($query); // Set the prepared statement to process the query
            $user_id = $dbLink->cleanInput($user_id); // Sanitize the variables before sending it to the server
            $book_id = $dbLink->cleanInput($book_id); // Sanitize the variables before sending it to the server
            $stmt->bind_param("ss", $user_id, $book_id); // Bind the query's parameters
			//用bind_param，去將我們的變數，與「?」做結合，而「”s”」，代表的是string
            $stmt->execute(); // Execute the prepared statement
            $rs = $stmt->get_result(); // Bind and store the query result
			
			$stmt = $dbLink->getConnection()->prepare($check_query);
            $user_id = $dbLink->cleanInput($user_id); // Sanitize the variables before sending it to the server
            $book_id = $dbLink->cleanInput($book_id); // Sanitize the variables before sending it to the server
            $stmt->bind_param("ss", $user_id, $book_id);
            $stmt->execute();
            $rs = $stmt->get_result();
			$row=$rs->fetch_assoc();
			if ( $row !=""){
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
        return ($success);///////////
    }
}
?>