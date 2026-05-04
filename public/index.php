<?php

use App\Converter\CsvConverter;
use App\Converter\PhpArrayConverter;
use App\Converter\SqlConverter;
use App\Converter\TypeScriptConverter;
use App\Converter\YamlConverter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addErrorMiddleware(false, true, true);

$app->get('/', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/index.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->post('/convert', function (Request $request, Response $response) {
    $body  = json_decode((string) $request->getBody(), true) ?? [];
    $input = $body['input'] ?? '';
    $to    = $body['to'] ?? 'yaml';

    if (strlen($input) > 102400) {
        $response->getBody()->write(json_encode(['output' => null, 'error' => 'Input exceeds 100KB limit']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(413);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response->getBody()->write(json_encode(['output' => null, 'error' => json_last_error_msg()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!is_array($data)) {
        $response->getBody()->write(json_encode(['output' => null, 'error' => 'Input must be a JSON object or array']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $converters = [
        'yaml'       => new YamlConverter(),
        'php'        => new PhpArrayConverter(),
        'typescript' => new TypeScriptConverter(),
        'sql'        => new SqlConverter(),
        'csv'        => new CsvConverter(),
    ];

    if (!isset($converters[$to])) {
        $response->getBody()->write(json_encode(['output' => null, 'error' => "Unknown format: $to"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $output = $converters[$to]->convert($data);
    $response->getBody()->write(json_encode(['output' => $output, 'error' => null]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
