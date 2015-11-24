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
    const VERSION           = '0.1';
    const HOST              = 'http://useradapted.herokuapp.com/'; // TODO: uitsplitsen per plugin
    const COOKIE_PREFIX     = '_ua';

    protected $api_key      = null;
    protected $client_id    = 0; // Current request client identifier

    public $plugin          = 'analyse/headers';
    public $profile         = []; // Current Profile

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

        if ($this->api_key == null) {
            throw new \InvalidArgumentException('Api key not set');
        }
    }

    protected function sendHeaders()
    {
        $headers = $this->buildHeaderArray();
        return $this->sendRequest(array('data' => ['headers' => $headers]));
    }

    public function updateProfile($data = []){
        $this->sendRequest($data);
    }

    public function sendRequest($data = [])
    {
        return $this->sendCurl($data);
    }

    public function sendCurl($data)
    {
        $data['identity']   = (int) $this->getIdentifier();
        $data['probe']      = 0;
        $data['request']    = $this->getRequestId();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::HOST.$this->plugin,
            CURLOPT_USERAGENT => 'UA V' . $this->version(),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),//            , 'Content-Length: ' . strlen(serialize($data))), -> werkt niet
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT  => 1,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_PORT => 80,
        ));

        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        if (FALSE === $resp)
            throw new \Exception(curl_error($curl), curl_errno($curl));

        // Close request to clear up some resources
        curl_close($curl);

        // Check if response is profile and possible set new identity
        $json =  json_decode($resp);
        if(isset($json->identity)){
            $this->profile = json_decode($resp);
            if(isset($this->profile->identity)){
                if($this->client_id == 0){
                    // If a new client id is generated set Cookie
                    $this->setIdentifier((int) $this->profile->identity);
                }
            }
        }
        return $resp;
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
     * Set the current plugin / path to send
     *
     * @param string $plugin
     */
    public function setPlugin($plugin = 'headers'){
        $this->plugin = 'analyse/'.$plugin;
    }


    /**
     * Build Header Array
     * @return array
     */
    protected function buildHeaderArray()
    {
        return $this->filterHeaders($_SERVER);
    }

    /**
     * Returns a filtered list of the headers
     * @param array $data
     * @return array
     */
    protected function filterHeaders($data = [])
    {
        $filtered = array();
        foreach ($data as $key => $value) {
            if (
                strpos($key, 'HTTP_') !== false ||
                $key == 'REMOTE_ADDR'
            ) {
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
    protected function setIdentifier($id = null)
    {
        $cn = self::COOKIE_PREFIX; // Cookie name
        $this->setCookie($cn, $id);
    }

    /**
     * Get Current client identifier
     *
     * @return null
     */
    protected function getIdentifier()
    {

        $cn = self::COOKIE_PREFIX; // Cookie name
        if ($this->getCookie($cn) != null) {
            $this->client_id = $this->getCookie($cn);
        } else {
            // TODO: rewrite -1, if need info fast;
            $this->client_id = 0;
        }
        return $this->client_id;
    }

    protected function getRequestId()
    {
        return $this->getNewRequestCount();
    }

    protected function getNewRequestCount()
    {
        $cn = self::COOKIE_PREFIX . 'r';
        if ($this->getCookie($cn) != null) {
            $old = $this->getCookie($cn);
        } else {
            $old = 0;
        }
        $new = $old + 1;
        $this->setCookie($cn, $new);
        return $new;
    }

    public function renderJS()
    {
        echo '<script type="text/javascript" crossorigin="anonymous" src="useradapted.js"></script>';
    }


    /**
     * Set Cookie
     *
     * @param $name
     * @param $value
     */
    public function setCookie($name, $value)
    {
        $expire = time() + 60 * 60 * 24 * 30; // 30 days
        setcookie($name, $value, $expire, "/");
    }

    /**
     * Set Cookie
     *
     * @param $name
     * @param $value
     */
    public function getCookie($name)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return null;
    }


    /* ------ PHP Helper Functions */

    public function isTech(){
        // TODO: Clean this up
        if(isset($this->profile)){
            if(isset($this->profile->skills)){
                if(isset($this->profile->skills->tech)){
                    if($this->profile->skills->tech > 128){
                        return true;
                    }
                }
            }
        }
        return false;
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
    if ($exit)
        exit;
}