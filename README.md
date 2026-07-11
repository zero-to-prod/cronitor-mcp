# cronitor-mcp

![](art/logo.png)

[![Repo](https://img.shields.io/badge/github-gray?logo=github)](https://github.com/zero-to-prod/cronitor-mcp)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/test.yml?label=test)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/backwards_compatibility.yml?label=backwards_compatibility)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/build_docker_image.yml?label=build_docker_image)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![GitHub License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](https://github.com/zero-to-prod/cronitor-mcp/blob/main/LICENSE.md)
[![Hits-of-Code](https://hitsofcode.com/github/zero-to-prod/cronitor-mcp?branch=main)](https://hitsofcode.com/github/zero-to-prod/cronitor-mcp/view?branch=main)

## Contents

- [Introduction](#introduction)
- [Quick Start](#quick-start)
- [Multiple Instances](#multiple-instances)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Docker Image](#docker)
- [Environment Variables](#environment-variables)
- [Contributing](#contributing)

## Introduction

MCP Server for Cronitor monitoring and observability

## Quick Start

Run the Docker image:

```shell
docker run -d -p 8090:80 \
  -e CRONITOR_API_KEY=your_cronitor_api_key_here \
  davidsmith3/cronitor-mcp:latest
```

Add the server to Claude:

```shell
claude mcp add --transport http cronitor http://localhost:8090/mcp
```

Optionally, add the server directly:

```json
{
    "mcpServers": {
        "cronitor": {
            "type": "streamable-http",
            "url": "http://localhost:8090/mcp"
        }
    }
}
```

## Multiple Instances

This MCP server supports running multiple independent instances from the same Docker image by mounting different controller directories.

### Architecture Benefits

- **Single Image, Multiple Servers**: Build once, run many times with different configurations
- **Isolated Sessions**: Each instance maintains separate session storage
- **Dynamic Controllers**: Mount any PHP controllers at runtime without rebuilding
- **Easy Scaling**: Spin up new instances by changing port and mount path

### Running Multiple Instances

```bash
# Instance 1: Cronitor monitoring
docker run -d --name mcp-cronitor -p 8081:80 \
  -v ~/mcp-servers/cronitor/controllers:/app/controllers:ro \
  -e MCP_SERVER_NAME=cronitor \
  -e MCP_CONTROLLER_PATHS=controllers \
  -e CRONITOR_API_KEY=your_key \
  davidsmith3/cronitor-mcp:latest

# Instance 2: Weather tools (different controllers)
docker run -d --name mcp-weather -p 8082:80 \
  -v ~/mcp-servers/weather/controllers:/app/controllers:ro \
  -e MCP_SERVER_NAME=weather \
  -e MCP_CONTROLLER_PATHS=controllers \
  davidsmith3/cronitor-mcp:latest

# Instance 3: Database utilities
docker run -d --name mcp-database -p 8083:80 \
  -v ~/mcp-servers/database/controllers:/app/controllers:ro \
  -e MCP_SERVER_NAME=database-utils \
  -e MCP_CONTROLLER_PATHS=controllers \
  davidsmith3/cronitor-mcp:latest
```

Each instance runs independently with different:
- **Ports** (8081, 8082, 8083, etc.)
- **Controllers** (mounted from different directories)
- **Names** (cronitor, weather, database-utils)
- **Sessions** (isolated storage)

For detailed examples and best practices, see [DOCKER_USAGE.md](./DOCKER_USAGE.md).

## Requirements

- PHP 8.1 or higher

## Installation

```bash
composer require zero-to-prod/cronitor-mcp
```

## Usage

```shell
vendor/bin/cronitor-mcp list
```

## Docker

Run using the [Docker image](https://hub.docker.com/repository/docker/davidsmith3/cronitor-mcp):

```shell
docker run -d -p 8080:80 \
  -e CRONITOR_API_KEY=your_cronitor_api_key_here \
  davidsmith3/cronitor-mcp:latest
```

## Environment Variables

Configure the MCP server behavior using environment variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `MCP_SERVER_NAME` | `MCP Server` | Display name shown in Claude Desktop |
| `MCP_CONTROLLER_PATHS` | `app/Http/Controllers` | Colon-separated paths to discover controllers |
| `MCP_SESSIONS_DIR` | `/app/storage/mcp-sessions` | Directory for session storage |
| `APP_VERSION` | `0.0.0` | Application version displayed in server info |
| `APP_DEBUG` | `false` | Enable debug logging (`true` or `false`) |
| `CRONITOR_API_KEY` | - | Your Cronitor API key ([get it here](https://cronitor.io/settings/api)) |

### Example with Custom Configuration

```shell
docker run -d -p 8080:80 \
  -e MCP_SERVER_NAME=my-cronitor \
  -e CRONITOR_API_KEY=your_cronitor_api_key_here \
  -e APP_DEBUG=true \
  davidsmith3/cronitor-mcp:latest
```

### Multiple Controller Paths

Specify multiple controller directories using colon separation:

```shell
docker run -d -p 8080:80 \
  -v ~/controllers1:/app/controllers1:ro \
  -v ~/controllers2:/app/controllers2:ro \
  -e MCP_CONTROLLER_PATHS=controllers1:controllers2 \
  davidsmith3/cronitor-mcp:latest
```

### Persistent Sessions

```shell
docker run -d -p 8080:80 \
  -e CRONITOR_API_KEY=your_cronitor_api_key_here \
  -v mcp-sessions:/app/storage/mcp-sessions \
  davidsmith3/cronitor-mcp:latest
```

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md)

## Links

- [Docker Usage Guide](./DOCKER_USAGE.md) - Comprehensive guide for running multiple instances
- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)