<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\ToolAnnotations;
use RuntimeException;

/**
 * Cronitor Issues API Tools
 *
 * This controller provides MCP tools for interacting with the Cronitor Issues API.
 * See: https://cronitor.io/docs/issues-api
 */
class IssuesController
{

    #[McpTool(
        name: 'issues',
        description: <<<TEXT
            Lists all issues from Cronitor with comprehensive filtering and pagination support. 
            This tool retrieves issues from your Cronitor account. 
            Issues represent incidents that can be tracked through their lifecycle, posted to status pages, and used for team communication about service disruptions or maintenance events.
            
            FILTERING BEHAVIOR:
            - Multiple values for the same parameter (comma-separated) are treated as OR operations
              Example: state="unresolved,investigating" returns issues in EITHER state
            - Different parameters are combined with AND logic
              Example: state="unresolved" + severity="outage" returns unresolved issues that are also outages
            - All filters are optional; omitting all filters returns all issues
            TEXT,
        annotations: new ToolAnnotations(
            title: 'Cronitor Issues',
            readOnlyHint: true
        )
    )]
    public function issues(
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by issue lifecycle state. Comma-separated list for multiple values (OR logic).
                Valid states:
                  - "unresolved": Issue is open and unaddressed (default state for new issues)
                  - "investigating": Team is actively investigating the issue
                  - "identified": Root cause has been identified but not yet fixed
                  - "monitoring": Fix has been implemented and is being monitored
                  - "resolved": Issue is fully resolved and closed
                  - "update": General status update (used for issue updates, not primary state)
                Examples: "unresolved", "unresolved,investigating", "resolved"
                TEXT,
            pattern: '^(unresolved|investigating|identified|monitoring|resolved|update)(,(unresolved|investigating|identified|monitoring|resolved|update))*$'
        )]
        ?string $state = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by issue severity/impact level. Comma-separated list for multiple values (OR logic).
                Severity affects prioritization and display prominence on status pages.
                Valid severities (ordered from lowest to highest impact):
                  - "missing_data": Data collection or telemetry issues (informational)
                  - "operational": Normal operations, no disruption (informational)
                  - "maintenance": Planned maintenance window (no unexpected impact)
                  - "degraded_performance": Service is slower than normal but functional
                  - "minor_outage": Partial service disruption affecting some users/features
                  - "outage": Major service disruption affecting all or most users (default when creating issues)
                Examples: "outage", "outage,minor_outage", "degraded_performance"
                TEXT,
            pattern: '^(missing_data|operational|maintenance|degraded_performance|minor_outage|outage)(,(missing_data|operational|maintenance|degraded_performance|minor_outage|outage))*$'
        )]
        ?string $severity = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by status page identifier. Only returns issues associated with the specified status page.
                Use the exact status page identifier/key from your Cronitor account.
                Example: "my-status-page-key"
                TEXT,
            minLength: 1
        )]
        ?string $statuspage = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by monitor group key(s). Comma-separated list for multiple groups (OR logic).
                Returns issues associated with monitors in the specified group(s).
                Example: "production-services", "api-monitors,database-monitors"
                TEXT,
            minLength: 1
        )]
        ?string $group = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by job monitor key(s). Comma-separated list for multiple jobs (OR logic).
                Returns issues related to the specified cron job or scheduled task monitors.
                Example: "daily-backup-job", "hourly-sync,daily-report"
                TEXT,
            minLength: 1
        )]
        ?string $job = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by component key(s). Comma-separated list for multiple components (OR logic).
                Components represent logical parts of your infrastructure (e.g., API, Database, CDN).
                Example: "api-gateway", "payment-processor,database"
                TEXT,
            minLength: 1
        )]
        ?string $component = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by check monitor key(s). Comma-separated list for multiple checks (OR logic).
                Returns issues from the specified health check or API check monitors.
                Example: "api-health-check", "login-check,signup-check"
                TEXT,
            minLength: 1
        )]
        ?string $check = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by heartbeat monitor key(s). Comma-separated list for multiple heartbeats (OR logic).
                Returns issues from the specified heartbeat/ping monitors.
                Example: "worker-heartbeat", "cron-pulse,background-job-pulse"
                TEXT,
            minLength: 1
        )]
        ?string $heartbeat = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by site monitor key(s). Comma-separated list for multiple sites (OR logic).
                Returns issues from website uptime monitors.
                Example: "main-website", "app-site,marketing-site"
                TEXT,
            minLength: 1
        )]
        ?string $site = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by monitor tag(s). Comma-separated list for multiple tags (OR logic).
                Returns issues from monitors that have the specified tag(s).
                Example: "critical", "production,staging"
                TEXT,
            minLength: 1
        )]
        ?string $tag = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by monitor type(s). Comma-separated list for multiple types (OR logic).
                Valid types: "check", "heartbeat", "job", "site"
                Example: "check", "job,heartbeat"
                TEXT,
            pattern: '^(check|heartbeat|job|site)(,(check|heartbeat|job|site))*$'
        )]
        ?string $type = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by environment key. Returns issues associated with the specified environment.
                Must match an environment key configured in your Cronitor account.
                Example: "production", "staging", "development"
                TEXT,
            minLength: 1
        )]
        ?string $env = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Free-text search across issue names, descriptions, monitor names, and monitor keys.
                Case-insensitive partial matching. Useful for finding issues by keyword.
                Example: "database timeout", "payment", "API error"
                TEXT,
            minLength: 1
        )]
        ?string $search = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Filter by time range relative to now. Only returns issues that started within this window.
                Format: number followed by unit (h=hours, d=days, w=weeks, m=months, y=years)
                Common examples: "24h" (last 24 hours), "7d" (last 7 days), "30d" (last 30 days)
                Additional examples: "12h", "2w", "3m", "1y"
                TEXT,
            pattern: '^\d+[hdwmy]$'
        )]
        ?string $time = null,
        #[Schema(
            type: 'string',
            description: <<<TEXT
                Sort order for results. Determines how issues are ordered in the response.
                Valid values:
                  - "started": Oldest issues first (ascending by start time)
                  - "-started": Newest issues first (descending by start time, most recent first)
                  - "relevance": Most relevant to search query first (only useful with search parameter)
                  - "-relevance": Least relevant first (rarely used)
                Default: "-started" (newest first)
                Example: "-started"
                TEXT,
            enum: ['started', '-started', 'relevance', '-relevance']
        )]
        ?string $orderBy = null,
        #[Schema(
            type: 'integer',
            description: <<<TEXT
                Page number for pagination (1-based indexing). Use with pageSize to paginate through results.
                Default: 1 (first page)
                Example: 1, 2, 3, etc.
                TEXT,
            minimum: 1
        )]
        ?int $page = null,
        #[Schema(
            type: 'integer',
            description: <<<TEXT
                Number of issues to return per page. Maximum value is 1000.
                Default: 50 (if not specified)
                Use smaller values (10-50) for faster responses, larger values (100-1000) to reduce API calls.
                Example: 50, 100, 500
                TEXT,
            minimum: 1,
            maximum: 1000
        )]
        ?int $pageSize = null,
        #[Schema(
            type: 'boolean',
            description: <<<TEXT
                When true, expands response to include detailed status page information for each issue.
                Includes: status page name, URL, visibility settings, branding, subscribers, etc.
                Default: false (minimal status page data included)
                Set to true when you need full status page context.
                TEXT
        )]
        ?bool $withStatusPageDetails = null,
        #[Schema(
            type: 'boolean',
            description: <<<TEXT
                When true, expands response to include detailed monitor information for each issue.
                Includes: monitor configuration, thresholds, notification settings, history, etc.
                Default: false (minimal monitor data included)
                Set to true when you need to understand monitor configuration or behavior.
                TEXT
        )]
        ?bool $withMonitorDetails = null,
        #[Schema(
            type: 'boolean',
            description: <<<TEXT
                When true, expands response to include detailed alert history for each issue.
                Includes: all alerts triggered, notification channels used, delivery status, timestamps, etc.
                Default: false (minimal alert data included)
                Set to true when you need to audit notifications or understand alert timeline.
                TEXT
        )]
        ?bool $withAlertDetails = null,
        #[Schema(
            type: 'boolean',
            description: <<<TEXT
                When true, expands response to include detailed component information for each issue.
                Includes: component status, dependencies, affected services, historical data, etc.
                Default: false (minimal component data included)
                Set to true when analyzing infrastructure impact or component health.
                TEXT
        )]
        ?bool $withComponentDetails = null
    ): array {
        $query_params = array_filter([
            'state' => $state,
            'severity' => $severity,
            'statuspage' => $statuspage,
            'group' => $group,
            'job' => $job,
            'component' => $component,
            'check' => $check,
            'heartbeat' => $heartbeat,
            'site' => $site,
            'tag' => $tag,
            'type' => $type,
            'env' => $env,
            'search' => $search,
            'time' => $time,
            'orderBy' => $orderBy,
            'page' => $page,
            'pageSize' => $pageSize,
            'withStatusPageDetails' => $withStatusPageDetails ? 'true' : null,
            'withMonitorDetails' => $withMonitorDetails ? 'true' : null,
            'withAlertDetails' => $withAlertDetails ? 'true' : null,
            'withComponentDetails' => $withComponentDetails ? 'true' : null,
        ], fn($v) => $v !== null);

        $url = 'https://cronitor.io/api/issues';
        if (!empty($query_params)) {
            $url .= '?'.http_build_query($query_params);
        }

        return $this->response($url);
    }

    /**
     * @param  string  $url
     *
     * @return array|mixed
     */
    protected function response(string $url): mixed
    {
        $api_key = $_ENV['CRONITOR_API_KEY'] ?? throw new RuntimeException('CRONITOR_API_KEY environment variable is not set');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $api_key.':');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Cronitor-MCP/1.0'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('cURL request failed: '.$curl_error);
        }

        if ($http_code !== 200) {
            throw new RuntimeException(
                sprintf('Cronitor API returned HTTP %d: %s', $http_code, $response)
            );
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON response: '.json_last_error_msg());
        }

        return $decoded ?? [];
    }
}