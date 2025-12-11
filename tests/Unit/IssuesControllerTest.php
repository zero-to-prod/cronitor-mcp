<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\IssuesController;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class IssuesControllerTest extends TestCase
{
    private string $original_api_key;
    private array $stub_response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->original_api_key = $_ENV['CRONITOR_API_KEY'] ?? '';
        $this->stub_response = json_decode(
            file_get_contents(__DIR__ . '/../stubs/cronitor_response.json'),
            true
        );
    }

    protected function tearDown(): void
    {
        if ($this->original_api_key) {
            $_ENV['CRONITOR_API_KEY'] = $this->original_api_key;
        } else {
            unset($_ENV['CRONITOR_API_KEY']);
        }
        parent::tearDown();
    }

    private function createMockController(array $response): IssuesController
    {
        return new class($response) extends IssuesController {
            public function __construct(private array $mock_response)
            {
            }

            protected function response(string $url): mixed
            {
                return $this->mock_response;
            }
        };
    }

    #[Test]
    public function issues_throws_exception_when_api_key_not_set(): void
    {
        unset($_ENV['CRONITOR_API_KEY']);
        $controller = new IssuesController();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CRONITOR_API_KEY environment variable is not set');

        $controller->issues();
    }

    #[Test]
    public function issues_returns_array_with_valid_api_key(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';
        $controller = $this->createMockController($this->stub_response);

        $result = $controller->issues();

        $this->assertIsArray($result);
    }

    #[Test]
    public function issues_returns_paginated_response_structure(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';
        $controller = $this->createMockController($this->stub_response);

        $result = $controller->issues(page: 1, pageSize: 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('page_size', $result);
        $this->assertArrayHasKey('total_count', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(1, $result['page']);
    }

    #[Test]
    public function issues_returns_expected_response_structure(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';
        $controller = $this->createMockController($this->stub_response);

        $result = $controller->issues();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('page_size', $result);
        $this->assertArrayHasKey('total_count', $result);
        $this->assertArrayHasKey('data', $result);
    }

    #[Test]
    public function issues_passes_through_api_response_data(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';
        $controller = $this->createMockController($this->stub_response);

        $result = $controller->issues();

        $this->assertEquals($this->stub_response, $result);
    }

    #[Test]
    public function issues_filters_exclude_null_values(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';

        $controller = new class($this->stub_response) extends IssuesController {
            private array $captured_url = [];

            public function __construct(private array $mock_response)
            {
            }

            protected function response(string $url): mixed
            {
                $this->captured_url[] = $url;
                return $this->mock_response;
            }

            public function getLastUrl(): string
            {
                return end($this->captured_url);
            }
        };

        $controller->issues(
            state: 'resolved',
            severity: null,
            statuspage: null
        );

        $url = $controller->getLastUrl();
        $this->assertStringContainsString('state=resolved', $url);
        $this->assertStringNotContainsString('severity', $url);
        $this->assertStringNotContainsString('statuspage', $url);
    }

    #[Test]
    public function issues_builds_query_string_with_multiple_parameters(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';

        $controller = new class($this->stub_response) extends IssuesController {
            private array $captured_url = [];

            public function __construct(private array $mock_response)
            {
            }

            protected function response(string $url): mixed
            {
                $this->captured_url[] = $url;
                return $this->mock_response;
            }

            public function getLastUrl(): string
            {
                return end($this->captured_url);
            }
        };

        $controller->issues(
            state: 'unresolved',
            severity: 'outage',
            page: 2,
            pageSize: 50
        );

        $url = $controller->getLastUrl();
        $this->assertStringContainsString('state=unresolved', $url);
        $this->assertStringContainsString('severity=outage', $url);
        $this->assertStringContainsString('page=2', $url);
        $this->assertStringContainsString('pageSize=50', $url);
    }

    #[Test]
    public function issues_includes_true_boolean_parameters_in_query(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';

        $controller = new class($this->stub_response) extends IssuesController {
            private array $captured_url = [];

            public function __construct(private array $mock_response)
            {
            }

            protected function response(string $url): mixed
            {
                $this->captured_url[] = $url;
                return $this->mock_response;
            }

            public function getLastUrl(): string
            {
                return end($this->captured_url);
            }
        };

        $controller->issues(withMonitorDetails: true);

        $url = $controller->getLastUrl();
        $this->assertStringContainsString('withMonitorDetails', $url);
    }

    #[Test]
    public function issues_excludes_false_boolean_parameters_from_query(): void
    {
        $_ENV['CRONITOR_API_KEY'] = 'test-api-key';

        $controller = new class($this->stub_response) extends IssuesController {
            private array $captured_url = [];

            public function __construct(private array $mock_response)
            {
            }

            protected function response(string $url): mixed
            {
                $this->captured_url[] = $url;
                return $this->mock_response;
            }

            public function getLastUrl(): string
            {
                return end($this->captured_url);
            }
        };

        $controller->issues(withAlertDetails: false);

        $url = $controller->getLastUrl();
        $this->assertStringNotContainsString('withAlertDetails', $url);
    }
}
