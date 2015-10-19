<?php namespace UserAdapted;

use React\EventLoop\LibEventLoop;


/**
 * Main UserAdapted class
 */
class UserAdapted
{
    /**
     * UserAdapted version
     */
    const VERSION = '0.1';
    const HOST = 'localhost';
    const COOKIE_PREFIX = '_ua';

    protected $api_key = null;

    protected $client_id = null; // Current request client identifier

    /**
     * Initiate UserAdapted
     *
     * With this function first request is made to server
     * and the response will include a first guess for the
     * current persona
     * @param array $params
     */
    public function init($params = [])
    {
        $this->readParams($params);
        $this->sendHeaders();
    }

    protected function readParams($params = [])
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }

        if($this->api_key == null){
            throw new \InvalidArgumentException('Api key not set');
        }
    }

    protected function sendHeaders()
    {
        $headers = $this->buildHeaderArray();
        $this->sendRequest(array('plugin' => 'request_headers', 'data' => $headers));
    }

    protected function buildHeaderArray()
    {
       return $this->filterHeaders($_SERVER);
    }

    protected function sendRequest($data = [])
    {


        $this->sendCurl($data);
    }

    public function sendCurl($data){
        $data['id']      = $this->getIdentifier();
        $data['request'] = $this->getRequestId();

        // Get cURL resource
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::HOST,
            CURLOPT_USERAGENT => 'UA V'.$this->version(),
            CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Content-Length: ' . strlen(serialize($data))),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data)
        ));
        // Send the request & save response to $resp
        echo json_encode($data);
//        $resp = curl_exec($curl);
        // Close request to clear up some resources
//        curl_close($curl);
    }


    /**
     * Returns current version, is hardcoded tested against, to keep tests up-to-date
     * @return string
     */
    public function version()
    {
        return self::VERSION;
    }




    /**
     * Returns a filtered list of the headers
     * @param array $data
     * @return array
     */
    protected function filterHeaders($data = []){
        $filtered = array();
        foreach($data as $key => $value){
            if(
                    strpos($key, 'HTTP_') !== false ||
                    $key == 'REMOTE_ADDR'
                ){
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }


    /**
     * Set Current client identifier and remembers it
     *
     * @param $id (first request id)
     */
    protected function setIdentifier($id = null){
        $cn = self::COOKIE_PREFIX; // Cookie name
        $this->setCookie($cn, $id);
    }

    /**
     * Get Current client identifier
     *
     * @return null
     */
    protected function getIdentifier(){
        $cn = self::COOKIE_PREFIX; // Cookie name
        if($this->getCookie($cn) != null){
            $this->client_id = $this->getCookie($cn);
        }
        else{
           $this->client_id = $this->generateIdentifier();
        }
        return $this->client_id;
    }

    /**
     * Create unique and random identifier
     * @return string
     */
    protected function generateIdentifier()
    {
        $id = time(). mt_rand();
        $this->setIdentifier($id);
        return $id;
    }

    protected function getRequestId(){
        return $this->getNewRequestCount();
    }

    protected function getNewRequestCount(){
        $cn = self::COOKIE_PREFIX.'r';
        if($this->getCookie($cn) != null){
            $old = $this->getCookie($cn);
        }
        else{
            $old = 0;
        }
        $new = $old + 1;
        $this->setCookie($cn, $new);
        return $new;
    }

    public function renderJS(){
        echo '<script type="text/javascript" crossorigin="anonymous" src="useradapted.js"></script>';
    }


    /**
     * Set Cookie
     *
     * @param $name
     * @param $value
     */
    public function setCookie($name, $value){
        $expire = time()+60*60*24*30; // 30 days
        setcookie($name, $value, $expire, "/");
    }

    /**
     * Set Cookie
     *
     * @param $name
     * @param $value
     */
    public function getCookie($name){
        if(isset($_COOKIE[$name])){
            return $_COOKIE[$name];
        }
        return null;
    }




}

/**
 *  DEV FUNCTIONS
 */
function dd($var)
{
    var_dump($var);
    exit;
}

function json($var, $exit = true)
{
    echo json_encode($var);
    if($exit)
        exit;
}