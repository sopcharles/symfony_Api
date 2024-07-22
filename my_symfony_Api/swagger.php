<?php

require 'vendor/autoload.php';

use OpenApi\Generator;

$openapi = Generator::scan(['src/ApiResource']);
header('Content-Type: application/json');
echo $openapi->toJson();
