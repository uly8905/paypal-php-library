<?php

require_once('../../../autoload.php');
require_once('../../../includes/config.php');

$configArray = array(
                'ClientID' => $rest_client_id,
                'ClientSecret' => $rest_client_secret,
                'LogResults' => $log_results, 
                'LogPath' => $log_path,
                'LogLevel' => $log_level  
                );

$PayPal = new \angelleye\PayPal\rest\notifications\NotificationsAPI($configArray);

$returnArray = $PayPal->webhooks_event_types();

echo "<pre>";
print_r($returnArray);