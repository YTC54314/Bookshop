<?php
// API v.1 token: ZGV2czppZHNs

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method");
header("Content-Type: application/json;charset=UTF-8");
abstract class RestAPI {
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT, or DELETE
    */
    protected $method = '';

    /**
     * Property: endpoint
     * The model requested in the URI. e.g.: /user
    */
    protected $endpoint = '';

    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can not be handled by the basic methods.
     * e.g.: /user/register
    */
    protected $verb = '';

    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in this case an Integer ID for the resource.
     * e.g.: /<endpoint>/<verb>/<arg0>/<arg1> or /<endpoint>/<arg0>
    */
    protected $args = array();

    /**
     * Property: file
     * Store the input of the PUT request
    */
    protected $file = null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble, and pre-process the data
    */
    public function __construct($request) {
        $this->_preFlightCheck();
		$this->args = explode('/', rtrim($request,'/'));//split url by /
		$this->args = array_slice($this->args, 1);//第二个元素"開始"取出，並返回之後其餘元素
		$this->endpoint = array_shift($this->args);//array_shift->刪除arg[0]值///endpoint->被刪除的那個值->arg[0]
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method=='POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD']=='DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD']=='PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch ($this->method) {
            case 'DELETE': // used to delete an entry in the collection
                // In most cases, the DELETE method would not asked the client to provide any parameter.
                // In case the DELETE method use any parameter, the received arguments need to be cleaned first.
                // $this->request = $this->_cleanInputs($_POST);
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'POST': // used to create a new entry in the collection
                $this->request = $this->_cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'PUT': // used to modify existing entry in the collection
                //$this->request = $this->_cleanInputs($_POST);
                $this->request = $this->_cleanInputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            default:
                $this->_response('Method Not Allowed', 405);
                break;
        }
    }

    public function processAPI() {
		if (method_exists($this, $this->endpoint)) {
			return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response("No Endpoint: " . $this->endpoint, 404);
    }

    public function getToken() {
        // variable that holds the credential passed by user
        $token = 'ZGV2czppZHNs';
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			// mod_php
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PWD'];
            $token = base64_encode("$username:$password");
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			// most other servers
            if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic')===0) {
                $token = base64_encode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
            }
        }
        if (is_null($token)) {
			header('HTTP/1.0 401 Unauthorized');
            return false;
        }
        return $token;
    }

    // Validate the token from database
    public function authorizeToken($token) {
        return ($token==="ZGV2czppZHNs");
    }

    private function _preFlightCheck() {
        // respond to preFlights
        if ($_SERVER['REQUEST_METHOD']=='OPTIONS') {
            // return only the headers and not the content
            // only allow CORS if we are doing GET - i.e. no saving for now
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']=='GET') {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                header('Access-Control-Allow-Headers: X-Requested-With, Authorization, Origin, Accept, Content-Type');
            }
            exit;
        }
    }

    private function _cleanInputs($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $clean_input[$key] = $this->_cleanInputs($value);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _response($data, $status=200) {
        header('HTTP/1.1 ' . $status . ' ' . $this->_requestStatus($status));
        return ($data);
    }

    private function _requestStatus($code) {
        $status = array(
            '100'=>'Continue',
            '101'=>'Switching Protocols',
            '200'=>'OK',
            '201'=>'Created',
            '202'=>'Accepted',
            '203'=>'Non-Authoritative Information',
            '204'=>'No Content',
            '205'=>'Reset Content',
            '206'=>'Partial Content',
            '300'=>'Multiple Choices',
            '301'=>'Moved Permanently',
            '302'=>'Found',
            '303'=>'See Other',
            '304'=>'Not Modified',
            '305'=>'Use Proxy',
            '306'=>'(Unused)',
            '307'=>'Temporary Redirect',
            '400'=>'Bad Request',
            '401'=>'Unauthorized',
            '402'=>'Payment Required',
            '403'=>'Forbidden',
            '404'=>'Not Found',
            '405'=>'Method Not Allowed',
            '406'=>'Not Acceptable',
            '407'=>'Proxy Authentication Required',
            '408'=>'Request Timeout',
            '409'=>'Conflict',
            '410'=>'Gone',
            '411'=>'Length Required',
            '412'=>'Precondition Failed',
            '413'=>'Request Entity Too Large',
            '414'=>'Request-URI Too Long',
            '415'=>'Unsupported Media Type',
            '416'=>'Requested Range Not Satisfiable',
            '417'=>'Expectation Failed',
            '500'=>'Internal Server Error',
            '501'=>'Not Implemented',
            '502'=>'Bad Gateway',
            '503'=>'Service Unavailable',
            '504'=>'Gateway Timeout',
            '505'=>'HTTP Version Not Supported',
        );
        return (isset($status[$code])? $status[$code] : $status[500]);
    }	
}
?>