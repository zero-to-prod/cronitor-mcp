# cronitor-mcp

> **Using this as a template?** Run `php configure.php` first to customize this repository for your project. See [TEMPLATE_SETUP.md](./TEMPLATE_SETUP.md) for details.

![](art/logo.png)

[![Repo](https://img.shields.io/badge/github-gray?logo=github)](https://github.com/zero-to-prod/cronitor-mcp)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/test.yml?label=test)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/backwards_compatibility.yml?label=backwards_compatibility)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/cronitor-mcp/build_docker_image.yml?label=build_docker_image)](https://github.com/zero-to-prod/cronitor-mcp/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/zero-to-prod/cronitor-mcp?color=blue)](https://packagist.org/packages/zero-to-prod/cronitor-mcp/stats)
[![php](https://img.shields.io/packagist/php-v/zero-to-prod/cronitor-mcp.svg?color=purple)](https://packagist.org/packages/zero-to-prod/cronitor-mcp/stats)
[![Packagist Version](https://img.shields.io/packagist/v/zero-to-prod/cronitor-mcp?color=f28d1a)](https://packagist.org/packages/zero-to-prod/cronitor-mcp)
[![License](https://img.shields.io/packagist/l/zero-to-prod/cronitor-mcp?color=pink)](https://github.com/zero-to-prod/cronitor-mcp/blob/main/LICENSE.md)
[![wakatime](https://wakatime.com/badge/github/zero-to-prod/cronitor-mcp.svg)](https://wakatime.com/badge/github/zero-to-prod/cronitor-mcp)
[![Hits-of-Code](https://hitsofcode.com/github/zero-to-prod/cronitor-mcp?branch=main)](https://hitsofcode.com/github/zero-to-prod/cronitor-mcp/view?branch=main)

## Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Docker Image](#docker)
- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)
- [Contributing](#contributing)

## Introduction

MCP Server for Cronitor monitoring and observability

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
docker run -d -p 8080:80 davidsmith3/cronitor-mcp:latest
```

### Environment Variables

- `MCP_DEBUG=false` - Enable debug mode

Example:

```shell
docker run -d -p 8080:80 \
  -e MCP_DEBUG=true \
  davidsmith3/cronitor-mcp:latest
```

### Persistent Sessions

```shell
docker run -d -p 8080:80 \
  -v mcp-sessions:/app/storage/mcp-sessions \
  davidsmith3/cronitor-mcp:latest
```

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md)

## Links

- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)