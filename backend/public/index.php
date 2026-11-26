<?php

use Illuminate\Http\Request;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->handleRequest(Request::capture());
