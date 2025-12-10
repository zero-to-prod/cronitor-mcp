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
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Docker Image](#docker)
- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)
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

### Environment Variables

- `CRONITOR_API_KEY` - Your Cronitor API key for authentication (get it from [Cronitor API Settings](https://cronitor.io/settings/api))
- `MCP_DEBUG=false` - Enable debug mode

Example:

```shell
docker run -d -p 8080:80 \
  -e CRONITOR_API_KEY=your_cronitor_api_key_here \
  -e MCP_DEBUG=true \
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

- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)