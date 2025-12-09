# :package_name

> **Using this as a template?** Run `php configure.php` first to customize this repository for your project. See [TEMPLATE_SETUP.md](./TEMPLATE_SETUP.md) for details.

![](art/logo.png)

[![Repo](https://img.shields.io/badge/github-gray?logo=github)](https://github.com/:github_org/:github_repo)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/:github_org/:github_repo/test.yml?label=test)](https://github.com/:github_org/:github_repo/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/:github_org/:github_repo/backwards_compatibility.yml?label=backwards_compatibility)](https://github.com/:github_org/:github_repo/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/:github_org/:github_repo/build_docker_image.yml?label=build_docker_image)](https://github.com/:github_org/:github_repo/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/:vendor_name/:package_name?color=blue)](https://packagist.org/packages/:vendor_name/:package_name/stats)
[![php](https://img.shields.io/packagist/php-v/:vendor_name/:package_name.svg?color=purple)](https://packagist.org/packages/:vendor_name/:package_name/stats)
[![Packagist Version](https://img.shields.io/packagist/v/:vendor_name/:package_name?color=f28d1a)](https://packagist.org/packages/:vendor_name/:package_name)
[![License](https://img.shields.io/packagist/l/:vendor_name/:package_name?color=pink)](https://github.com/:github_org/:github_repo/blob/main/LICENSE.md)
[![wakatime](https://wakatime.com/badge/github/:github_org/:github_repo.svg)](https://wakatime.com/badge/github/:github_org/:github_repo)
[![Hits-of-Code](https://hitsofcode.com/github/:github_org/:github_repo?branch=main)](https://hitsofcode.com/github/:github_org/:github_repo/view?branch=main)

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

:package_description

## Requirements

- PHP 8.1 or higher

## Installation

```bash
composer require :vendor_name/:package_name
```

## Usage

```shell
vendor/bin/:package_slug list
```

## Docker

Run using the [Docker image](https://hub.docker.com/repository/docker/:docker_registry_username/:docker_image_name):

```shell
docker run -d -p 8080:80 :docker_registry_username/:docker_image_name:latest
```

### Environment Variables

- `MCP_DEBUG=false` - Enable debug mode

Example:

```shell
docker run -d -p 8080:80 \
  -e MCP_DEBUG=true \
  :docker_registry_username/:docker_image_name:latest
```

### Persistent Sessions

```shell
docker run -d -p 8080:80 \
  -v mcp-sessions:/app/storage/mcp-sessions \
  :docker_registry_username/:docker_image_name:latest
```

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md)

## Links

- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Image Development](./IMAGE_DEVELOPMENT.md)