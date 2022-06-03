<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers;
Use App\Route;

Route\Web::post('/gendoc', [Controllers\ApiController::class, 'actionGenDoc']);

Route\Web::close();
