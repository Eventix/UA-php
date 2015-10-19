<?php


require __DIR__ . '/vendor/autoload.php';


$ua = new UserAdapted\UserAdapted;
$ua->init(array(
            'api_key' => '125',
            'app_id' => 'optional')
        );
echo '&nbsp;';
$ua->renderJS();

?>