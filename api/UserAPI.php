<?php 
include("RestAPI.php");
include("UserController.php");
class UserAPI extends RestAPI {
    private $userCtrl;

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->userCtrl = new UserController();
    }

    // The supported methods by the API
    public function users() {
        if ($this->method=="GET") {
            // return list of user(s)
            $username = (empty($this->request["username"])? "" : $this->request["username"]);
            if (empty($username)) {//all
                $users = $this->userCtrl->getUsers();
                $usersArr = array();
                foreach ($users as $user) {
                    array_push($usersArr, array(
                        "id"=>$user[0],
                        "username"=>$user[1],
                        "pin"=>$user[2],
                        "password"=>$user[3],
                        "first_name"=>$user[4],
                        "last_name"=>$user[5]));
                }
                return (array("error"=>false, "message"=>"", "users"=>$usersArr));
            } else {//one
				if(empty($username))
					return (array("error"=>true, "message"=>"Please fill up the blank"));
				else{
					$user = $this->userCtrl->getUser($username);//http://localhost/api/UserAPI.php/users?username=alice
					if (!is_null($user)) {
						$userArr = array(
							"id"=>$user[0],
							"username"=>$user[1],
							"pin"=>$user[2],
							"password"=>$user[3],
							"first_name"=>$user[4],
							"last_name"=>$user[5]);
						return array("error"=>false, "message"=>"", "user"=>$userArr);					
					} else {
						return (array("error"=>true, "message"=>"User not found"));
					}
				}
            }
        } else if ($this->method=="PUT") {
			// register new user or update the existing user's data
            // Insert the command for PUT method here
			if(empty($this->request["username"])||empty($this->request["password"])||empty($this->request["first_name"])||empty($this->request["last_name"]))
				return (array("error"=>true, "message"=>"Please fill up every blank"));
			else{
				$username = ($this->request["username"]);
				$password = ($this->request["password"]);
				$first_name = ($this->request["first_name"]);
				$last_name = ($this->request["last_name"]);
				$add_user = $this->userCtrl->putUser($username, $password, $first_name, $last_name);
				if (!is_null($add_user)) {
					$userArr = array(
						"id"=>$add_user[0],
						"username"=>$add_user[1],
						"first_name"=>$add_user[2],
						"last_name"=>$add_user[3],
						"last_modified_date"=>$add_user[4]);
					return (array("error"=>false, "message"=>"", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot update user"));
				}	
			}
        } else if ($this->method=="POST") {
			if(empty($this->request["username"])||empty($this->request["password"])||empty($this->request["first_name"])||empty($this->request["last_name"]))
				return (array("error"=>true, "message"=>"Please fill up every blank"));
			else{
				$username = ($this->request["username"]);
				$password = ($this->request["password"]);
				$first_name = ($this->request["first_name"]);
				$last_name = ($this->request["last_name"]);
				$add_user = $this->userCtrl->postUser($username, $password, $first_name, $last_name);
				if (!is_null($add_user)) {
					$userArr = array(
						"id"=>$add_user[0],
						"username"=>$add_user[1],
						"first_name"=>$add_user[2],
						"last_name"=>$add_user[3]);
					return (array("error"=>false, "message"=>"User has been successfully created", "add_user"=>$userArr));		
				} elseif($add_user==null) {
					return (array("error"=>true, "message"=>"Cannot add user"));
				}	
			}
        } else if ($this->method=="DELETE") {
            // Insert the command for DELETE method here
			if(empty($this->request["username"]))
				return (array("error"=>true, "message"=>"Please input the username"));
			else{
				$username = ($this->request["username"]);
				$delete_user = $this->userCtrl->deleteUser($username);
				if($delete_user==0)
					return (array("error"=>true, "message"=>""));
				if($delete_user==1)
					return (array("error"=>false, "message"=>"User data has been deleted"));				
			}
        }
    }
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
		$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
	}
	try {
		$API = new UserAPI($_SERVER['PATH_INFO'],$_SERVER['HTTP_ORIGIN']);
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