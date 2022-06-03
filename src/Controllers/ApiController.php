<?php

namespace App\Controllers;

use App\Core;

final class ApiController implements ControllerInterface
{
    public function actionGenDoc() : void
    {
        $response = [];

        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            http_response_code(400);
            $response = ['error' => ['code' => 400, 'message' => 'Bad Request: needed Content-Type: application/json']];
        } else {
            $request = json_decode(file_get_contents('php://input'), true);

            switch (true) {
                case empty($request):
                    http_response_code(400);
                    $response = ['error' => ['code' => 400, 'message' => 'Bad Request: body request is empty']];
                    break;
                case empty($request['URLTemplate']):
                    http_response_code(400);
                    $response = ['error' => ['code' => 400, 'message' => 'Bad Request: needed key URLTemplate']];
                    break;
                case ! preg_match('/^https:\/\/\S+\.xml$/', $request['URLTemplate']):
                    http_response_code(400);
                    $response = ['error' => ['code' => 400, 'message' => 'Bad Request: key URLTemplate needed to be xml file with https']];
                    break;
                case empty($request['RecordID']):
                    http_response_code(400);
                    $response = ['error' => ['code' => 400, 'message' => 'Bad Request: needed key RecordID']];
                    break;
                case $request['RecordID'] < 1 || $request['RecordID'] > 100:
                    http_response_code(400);
                    $response = ['error' => ['code' => 400, 'message' => 'Bad Request: key RecordID need to be int from 1 to 100']];
                    break;
            }

            $docParser = new Core\DocParser(file_get_contents($request['URLTemplate']));

            $response = $docParser->replace($request['RecordID'])->save();
        }

        print_r(json_encode($response, JSON_FORCE_OBJECT));
    }
}
