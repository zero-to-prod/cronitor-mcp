<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Log\AbstractLogger;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

// Simple logger for debugging
$logger = new class extends AbstractLogger {
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (($_ENV['MCP_DEBUG'] ?? 'false') === 'true') {
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
            error_log("[{$timestamp}] [{$level}] {$message}{$contextStr}");
        }
    }
};

// Ensure session directory exists
$sessions_dir = __DIR__ . '/storage/mcp-sessions';
if (!is_dir($sessions_dir) && !mkdir($sessions_dir, 0755, true) && !is_dir($sessions_dir)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $sessions_dir));
}

// Build server once
$server = Server::builder()
    ->setServerInfo('Cronitor MCP Server', '1.0.0')
    ->setDiscovery(__DIR__, ['app/Http/Controllers'])
    ->setSession(new FileSessionStore($sessions_dir))
    ->setLogger($logger)
    ->build();

// HTTP transport for web requests
$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
$request = $creator->fromGlobals();
$transport = new StreamableHttpTransport($request, logger: $logger);
$response = $server->run($transport);

// Emit response
(new SapiEmitter())->emit($response);