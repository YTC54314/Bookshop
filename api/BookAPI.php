<?php
include("RestAPI.php");
include("BookController.php");
class BookAPI extends RestAPI {
    private $userCtrl;

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->userCtrl = new BookController();
    }

    // The supported methods by the API
    public function books() {
        if ($this->method=="GET") {
            // return list of user(s)
            $username = (empty($this->request["username"])? "" : $this->request["username"]);
            if (empty($username)) {
                $users = $this->userCtrl->getUsers();
                $booksArr = array();
                foreach ($users as $user) {
                    array_push($booksArr, array(
                        "id"=>$user[0],
                        "title"=>$user[1],
						"author"=>$user[2],
						"isbn"=>$user[3],
						"abstract"=>$user[4],
						"publisher"=>$user[5],
						"publication_year"=>$user[6]));
                }
                return (array("error"=>false, "message"=>"", "users"=>$booksArr));
            } else {
                $user = $this->userCtrl->getUser($username);//http://localhost/api/UserAPI.php/users?username=alice
                if (!is_null($user)) {
                    $bookArr = array(
                        "id"=>$user[0],
                        "title"=>$user[1],
						"author"=>$user[2],
						"isbn"=>$user[3],
						"abstract"=>$user[4],
						"publisher"=>$user[5],
						"publication_year"=>$user[6]);
                    return array("error"=>false, "message"=>"", "user"=>$bookArr);					
                } else {
                    return (array("error"=>true, "message"=>"Book not found"));
                }
            }
        } else if ($this->method=="PUT") {
			if(empty($this->request["title"])||empty($this->request["author"])||empty($this->request["isbn"])||empty($this->request["publisher"])||empty($this->request["publication_year"])){
				echo(gettype($this->request["title"]));
				return (array("error"=>true, "message"=>"Please fill up every blank!!!!!!!!"));
			}
			
			else{
				$title = ($this->request["title"]);
				$author = ($this->request["author"]);
				$isbn = ($this->request["isbn"]);
				$abstract = ($this->request["abstract1"]);
				$publisher = ($this->request["publisher"]);
				$publication_year = ($this->request["publication_year"]);
				$add_user = $this->userCtrl->putUser($title, $author, $isbn, $abstract, $publisher, $publication_year);
				if (!is_null($add_user)) {
					$userArr = array(
						"id"=>$add_user[0],
						"title"=>$add_user[1],
						"author"=>$add_user[2],
						"last_modified_date"=>$add_user[3]
						);
					return (array("error"=>false, "message"=>"Book has been successfully updated", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot update book"));
				}	
			}
        } else if ($this->method=="POST") {
            // register new user or update the existing user's data
            // Insert the command for PUT method here
			if(empty($this->request["title"])||empty($this->request["author"])||empty($this->request["isbn"])||empty($this->request["publisher"])||empty($this->request["publication_year"])){
				echo(gettype($this->request["title"]));
				return (array("error"=>true, "message"=>"Please fill up every blank!!!!!!!!"));
			}
			
			else{
				$title = ($this->request["title"]);
				$author = ($this->request["author"]);
				$isbn = ($this->request["isbn"]);
				$abstract = ($this->request["abstract1"]);
				$publisher = ($this->request["publisher"]);
				$publication_year = ($this->request["publication_year"]);
				$add_user = $this->userCtrl->postUser($title, $author, $isbn, $abstract, $publisher, $publication_year);
				if (!is_null($add_user)) {
					$userArr = array(
						"id"=>$add_user[0],
						"title"=>$add_user[1],
						"author"=>$add_user[2]);
					return (array("error"=>false, "message"=>"Book has been successfully created", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot add book"));
				}	
			}
        } else if ($this->method=="DELETE") {
            // Insert the command for DELETE method here
			if(empty($this->request["username"]))
				return (array("error"=>true, "message"=>"Please input the bookname"));
			else{
				$username = ($this->request["username"]);
				$delete_user = $this->userCtrl->deleteUser($username);
				if($delete_user==0)
					return (array("error"=>true, "message"=>""));
				if($delete_user==1)
					return (array("error"=>false, "message"=>"Book's data has been deleted"));				
			}
        }
    }
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
		$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
	}
	try {
		$API = new BookAPI($_SERVER['PATH_INFO'],$_SERVER['HTTP_ORIGIN']);
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