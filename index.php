<?php


require __DIR__ . '/vendor/autoload.php';

function dd($var){
    var_dump($var);
    exit;
}

if(isset($_GET['plugin'])){
    if($_GET['plugin'] == 'javascript'){
        // TODO:: create fetch, like Queue::handler() from laravel
        $ua = new \UserAdapted\UserAdapted;
        $request_body = file_get_contents('php://input');
        $json = json_decode($request_body);
        $javascript_data = $json->data->javascript;
        $ua->setPlugin('javascript');
        $ua->updateProfile( ['data' => ['javascript' => (array) $javascript_data]]);
//        dd($ua->isTech());
        exit;
    }
}

$ua = new UserAdapted\UserAdapted;
$ua->init(array(
            'api_key' => '125',
            'app_id' => 'optional')
        );
echo '&nbsp;';
$ua->renderJS();

?>