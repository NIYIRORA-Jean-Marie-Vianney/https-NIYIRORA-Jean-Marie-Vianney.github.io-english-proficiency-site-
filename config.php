<?php
// Database settings. Defaults suit XAMPP on your own computer.
// On a web host, copy config.local.sample.php to config.local.php and fill in the values from your hosting panel.
// config.local.php is never uploaded to GitHub, so your password stays private.
// DB_NAME is an internal name; keep it unchanged on XAMPP so data from earlier versions carries over.
if(is_file(__DIR__.'/config.local.php'))require __DIR__.'/config.local.php';
defined('DB_HOST')||define('DB_HOST',getenv('DB_HOST')?:'127.0.0.1');
defined('DB_NAME')||define('DB_NAME',getenv('DB_NAME')?:'english_monitor');
defined('DB_USER')||define('DB_USER',getenv('DB_USER')!==false?getenv('DB_USER'):'root');
defined('DB_PASS')||define('DB_PASS',getenv('DB_PASS')!==false?getenv('DB_PASS'):'');
date_default_timezone_set(defined('APP_TZ')?APP_TZ:'Africa/Kigali');
