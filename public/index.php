<?php

use App\Converter\YamlConverter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/index.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->post('/convert', function (Request $request, Response $response) {
    $body  = json_decode((string) $request->getBody(), true) ?? [];
    $input = $body['input'] ?? '';
    $to    = $body['to'] ?? 'yaml';

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response->getBody()->write(json_encode(['output' => null, 'error' => json_last_error_msg()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $output = match ($to) {
        'yaml'  => (new YamlConverter())->convert($data),
        default => "Format '$to' not yet implemented",
    };

    $response->getBody()->write(json_encode(['output' => $output, 'error' => null]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
