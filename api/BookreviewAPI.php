<?php
include("RestAPI.php");
include("BookreviewController.php");
class BookreviewAPI extends RestAPI {
    private $userCtrl;

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->userCtrl = new BookreviewController();
    }

    // The supported methods by the API
    public function books() {
        if ($this->method=="GET") {
            // return list of user(s)
            $username = (empty($this->request["user_id"])? "" : $this->request["user_id"]);
            $bookname = (empty($this->request["book_id"])? "" : $this->request["book_id"]);
            if (empty($username)&&!empty($bookname)) {//有book_id
                $users = $this->userCtrl->getUsers_nouser($bookname);
                $booksArr = array();
				if (!empty($users)) {
					foreach ($users as $user) {
						array_push($booksArr, array(
							"user_id"=>$user[0],
							"book_id"=>$user[1],
							"rating"=>$user[2],
							"review"=>$user[3]
							));
					}
					return (array("error"=>false, "message"=>"", "users"=>$booksArr));
				} else {
                    return (array("error"=>true, "message"=>"Book review not found"));
                }
            }elseif(empty($bookname)&&!empty($username)){//有user_id
				$users = $this->userCtrl->getUsers_nobook($username);
                $booksArr = array();
				if (!empty($users)) {
					foreach ($users as $user) {
						array_push($booksArr, array(
							"user_id"=>$user[0],
							"book_id"=>$user[1],
							"rating"=>$user[2],
							"review"=>$user[3]
							));
					}
					return (array("error"=>false, "message"=>"", "users"=>$booksArr));
				} else {
                    return (array("error"=>true, "message"=>"Book review not found"));
                }
			}elseif(empty($bookname)&& empty($username)){//皆空
				$users = $this->userCtrl->getUser_both();
                $booksArr = array();
				if (!empty($users)) {
					foreach ($users as $user) {
						array_push($booksArr, array(
							"user_id"=>$user[0],
							"book_id"=>$user[1],
							"rating"=>$user[2],
							"review"=>$user[3]
							));
					}
					return (array("error"=>false, "message"=>"", "users"=>$booksArr));
				} else {
                    return (array("error"=>true, "message"=>"Book review not found"));
                }
			}else {//都有打
                $user = $this->userCtrl->getUser($username, $bookname);//http://localhost/api/BookreviewAPI.php/books?ueser_id=100&book_id=36
                if (!empty($user)) {
                    $bookArr = array(
                        "user_id"=>$user[0],
                        "book_id"=>$user[1],
						"rating"=>$user[2],
						"review"=>$user[3]
						);
                    return array("error"=>false, "message"=>"", "user"=>$bookArr);					
                } else {
                    return (array("error"=>true, "message"=>"Book review not found"));
                }
            }
        } else if ($this->method=="PUT") {
			if(empty($this->request["user_id"])||empty($this->request["book_id"])||empty($this->request["rating"])||empty($this->request["review"])){
				return (array("error"=>true, "message"=>"Please fill up every blank!!!!!!!!"));
			}
			else{
				$user_id = ($this->request["user_id"]);
				$book_id = ($this->request["book_id"]);
				$rating = ($this->request["rating"]);
				$review = ($this->request["review"]);
				$add_user = $this->userCtrl->putUser($user_id, $book_id, $rating, $review);
				if (!is_null($add_user)) {
					$userArr = array(
						"user_id"=>$add_user[0],
						"book_id"=>$add_user[1],
						"rating"=>$add_user[2],
						"review"=>$add_user[3],
						"last_modified_date"=>$add_user[4]);
					return (array("error"=>false, "message"=>"Review has been successfully updated", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot add the review", "add_user"=>""));
				}	
			}
        } else if ($this->method=="POST") {
            // register new user or update the existing user's data
            // Insert the command for PUT method here
			if(empty($this->request["user_id"])||empty($this->request["book_id"])||empty($this->request["rating"])||empty($this->request["review"])){
				return (array("error"=>true, "message"=>"Please fill up every blank!!!!!!!!"));
			}
			else{
				$user_id = ($this->request["user_id"]);
				$book_id = ($this->request["book_id"]);
				$rating = ($this->request["rating"]);
				$review = ($this->request["review"]);
				$add_user = $this->userCtrl->postUser($user_id, $book_id, $rating, $review);
				if (!is_null($add_user)) {
					$userArr = array(
						"user_id"=>$add_user[0],
						"book_id"=>$add_user[1],
						"rating"=>$add_user[2],
						"review"=>$add_user[3],
						"review_date"=>$add_user[4]);
					return (array("error"=>false, "message"=>"Review has been successfully created", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot add the review", "add_user"=>""));
				}	
			}
        } else if ($this->method=="DELETE") {
            // Insert the command for DELETE method here
				$book_id = ($this->request["book_id"]);
				$user_id = ($this->request["user_id"]);
				$delete = $this->userCtrl->deleteUser($user_id, $book_id);
				// print_r($delete);
				if($delete==0)
				{
					return (array("error"=>true, "message"=>""));
				}
				if($delete==1)
				{
					return (array("error"=>false, "message"=>"Book's data has been deleted"));				
				}
        }
    }
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
		$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
	}
	try {
		$API = new BookreviewAPI($_SERVER['PATH_INFO'],$_SERVER['HTTP_ORIGIN']);
		$token = $API->getToken();
		if($API->authorizeToken($token)) 
		{
			// print_r($API->processAPI());
			// $ss=$API->processAPI(); //array
			// echo $ss["users"][0]["id"];
			echo json_encode($API->processAPI());	
			// echo $result= json_encode(Array($API->processAPI()));
			// echo gettype($result); //string

		}
		else
		{
			header('HTTP/1.0 401 Unauthorized');
			throw new Exception('Unauthorized');
		}

	} catch (Exception $e) {
		echo json_encode(Array('error' => $e->getMessage()));
	}
?>