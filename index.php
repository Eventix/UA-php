<?php


require __DIR__ . '/vendor/autoload.php';

function dd($var){
    var_dump($var);
    exit;
}
$ua = new \UserAdapted\UserAdapted;
$ua->init(array(
        'key' => '24149614ZcSyhw2dWaRM8OrqmA42sYni0cRaxBoG907ssJKdxkcd3FcvcQsd9duCg',
        'user' => 'postman-pete'
    )
);

if(isset($_GET['plugin'])){
    if($_GET['plugin'] == 'javascript'){
        // TODO:: create fetch, like Queue::handler() from laravel
        $request_body = file_get_contents('php://input');
        $json = json_decode($request_body);
        $javascript_data = $json->data->javascript;
        $ua->setPlugin('javascript');
        $ua->updateProfile( ['data' => ['javascript' => (array) $javascript_data]]);
        dd($ua->isTech());
        exit;
    }
}
//
//$ua = new UserAdapted\UserAdapted;

echo '&nbsp;';
$ua->renderJS();
dd($ua->profile);
//dd();

?>