<?php
 include './vendor/autoload.php';

 $testConfig = dirname(__FILE__).DIRECTORY_SEPARATOR.'config';
 putenv("website_config_directory=$testConfig");

 echo "\n bootstrap - config:$testConfig\n";
 include 'website/templates/konfig.php';
