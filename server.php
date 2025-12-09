#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

// STDIO server for CLI/local MCP clients (e.g., Claude Desktop)
// For HTTP usage, use the /mcp endpoint via public/index.php

$server = Server::builder()
    ->setServerInfo('Cronitor MCP Server', '1.0.0')
    ->setDiscovery(__DIR__, ['/app/Http/Controllers'])
    ->build();

$transport = new StdioTransport();

$status = $server->run($transport);

exit($status);