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

    $inputSize = strlen($input);
    if ($inputSize > 102400) {
        $payload = json_encode(['output' => null, 'error' => 'Input exceeds 100KB limit']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(413);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $payload = json_encode(['output' => null, 'error' => json_last_error_msg()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!is_array($data)) {
        $payload = json_encode(['output' => null, 'error' => 'Input must be a JSON object or array']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $output = match ($to) {
            'yaml'       => (new YamlConverter())->convert($data),
            'php'        => (new PhpArrayConverter())->convert($data),
            'typescript' => (new TypeScriptConverter())->convert($data),
            'sql'        => (new SqlConverter())->convert($data),
            'csv'        => (new CsvConverter())->convert($data),
            default      => throw new \InvalidArgumentException("Unknown format: $to"),
        };
    } catch (\InvalidArgumentException $e) {
        $payload = json_encode(['output' => null, 'error' => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $response->getBody()->write(json_encode(['output' => $output, 'error' => null]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
