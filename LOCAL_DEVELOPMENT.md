# Local Development

## Prerequisites

- Docker

## Setup

Initialize the project:

```shell
sh dock init
```

Update dependencies:

```shell
sh dock composer update
```

## Testing

Run tests:

```shell
sh dock test
```

Test all PHP versions:

```shell
sh test.sh
```

## Configuration

Set PHP versions and Cronitor API key in `.env`:

```dotenv
PHP_VERSION=8.4
PHP_DEBUG=8.4
PHP_COMPOSER=8.4
CRONITOR_API_KEY=your_cronitor_api_key_here
```

If `.env` doesn't exist, run `sh dock init` to create it from `.env.example`.

Get your Cronitor API key from the [Cronitor API Settings page](https://cronitor.io/settings/api).