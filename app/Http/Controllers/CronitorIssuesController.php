<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\IssuesRequest;
use Mcp\Capability\Attribute\McpTool;
use RuntimeException;

/**
 * Cronitor Issues API Tools
 *
 * This controller provides MCP tools for interacting with the Cronitor Issues API.
 * See: https://cronitor.io/docs/issues-api
 */
class CronitorIssuesController
{
    /**
     * Lists all issues from Cronitor with comprehensive filtering and pagination support.
     *
     * This tool retrieves issues from your Cronitor account. Issues represent incidents that can be
     * tracked through their lifecycle, posted to status pages, and used for team communication about
     * service disruptions or maintenance events.
     *
     * FILTERING BEHAVIOR:
     * - Multiple values for the same parameter (comma-separated) are treated as OR operations
     *   Example: state="unresolved,investigating" returns issues in EITHER state
     * - Different parameters are combined with AND logic
     *   Example: state="unresolved" + severity="outage" returns unresolved issues that are also outages
     * - All filters are optional; omitting all filters returns all issues
     *
     * @param  string|null  $state
     *     Filter by issue lifecycle state. Comma-separated list for multiple values (OR logic).
     *     Valid states:
     *       - "unresolved": Issue is open and unaddressed (default state for new issues)
     *       - "investigating": Team is actively investigating the issue
     *       - "identified": Root cause has been identified but not yet fixed
     *       - "monitoring": Fix has been implemented and is being monitored
     *       - "resolved": Issue is fully resolved and closed
     *       - "update": General status update (used for issue updates, not primary state)
     *     Examples: "unresolved", "unresolved,investigating", "resolved"
     *
     * @param  string|null  $severity
     *     Filter by issue severity/impact level. Comma-separated list for multiple values (OR logic).
     *     Severity affects prioritization and display prominence on status pages.
     *     Valid severities (ordered from lowest to highest impact):
     *       - "missing_data": Data collection or telemetry issues (informational)
     *       - "operational": Normal operations, no disruption (informational)
     *       - "maintenance": Planned maintenance window (no unexpected impact)
     *       - "degraded_performance": Service is slower than normal but functional
     *       - "minor_outage": Partial service disruption affecting some users/features
     *       - "outage": Major service disruption affecting all or most users (default when creating issues)
     *     Examples: "outage", "outage,minor_outage", "degraded_performance"
     *
     * @param  string|null  $statuspage
     *     Filter by status page identifier. Only returns issues associated with the specified status page.
     *     Use the exact status page identifier/key from your Cronitor account.
     *     Example: "my-status-page-key"
     *
     * @param  string|null  $group
     *     Filter by monitor group key(s). Comma-separated list for multiple groups (OR logic).
     *     Returns issues associated with monitors in the specified group(s).
     *     Example: "production-services", "api-monitors,database-monitors"
     *
     * @param  string|null  $job
     *     Filter by job monitor key(s). Comma-separated list for multiple jobs (OR logic).
     *     Returns issues related to the specified cron job or scheduled task monitors.
     *     Example: "daily-backup-job", "hourly-sync,daily-report"
     *
     * @param  string|null  $component
     *     Filter by component key(s). Comma-separated list for multiple components (OR logic).
     *     Components represent logical parts of your infrastructure (e.g., API, Database, CDN).
     *     Example: "api-gateway", "payment-processor,database"
     *
     * @param  string|null  $check
     *     Filter by check monitor key(s). Comma-separated list for multiple checks (OR logic).
     *     Returns issues from the specified health check or API check monitors.
     *     Example: "api-health-check", "login-check,signup-check"
     *
     * @param  string|null  $heartbeat
     *     Filter by heartbeat monitor key(s). Comma-separated list for multiple heartbeats (OR logic).
     *     Returns issues from the specified heartbeat/ping monitors.
     *     Example: "worker-heartbeat", "cron-pulse,background-job-pulse"
     *
     * @param  string|null  $site
     *     Filter by site monitor key(s). Comma-separated list for multiple sites (OR logic).
     *     Returns issues from website uptime monitors.
     *     Example: "main-website", "app-site,marketing-site"
     *
     * @param  string|null  $tag
     *     Filter by monitor tag(s). Comma-separated list for multiple tags (OR logic).
     *     Returns issues from monitors that have the specified tag(s).
     *     Example: "critical", "production,staging"
     *
     * @param  string|null  $type
     *     Filter by monitor type(s). Comma-separated list for multiple types (OR logic).
     *     Valid types: "check", "heartbeat", "job", "site"
     *     Example: "check", "job,heartbeat"
     *
     * @param  string|null  $env
     *     Filter by environment key. Returns issues associated with the specified environment.
     *     Must match an environment key configured in your Cronitor account.
     *     Example: "production", "staging", "development"
     *
     * @param  string|null  $search
     *     Free-text search across issue names, descriptions, monitor names, and monitor keys.
     *     Case-insensitive partial matching. Useful for finding issues by keyword.
     *     Example: "database timeout", "payment", "API error"
     *
     * @param  string|null  $time
     *     Filter by time range relative to now. Only returns issues that started within this window.
     *     Format: number followed by unit (h=hours, d=days, w=weeks, m=months, y=years)
     *     Common examples: "24h" (last 24 hours), "7d" (last 7 days), "30d" (last 30 days)
     *     Additional examples: "12h", "2w", "3m", "1y"
     *
     * @param  string|null  $orderBy
     *     Sort order for results. Determines how issues are ordered in the response.
     *     Valid values:
     *       - "started": Oldest issues first (ascending by start time)
     *       - "-started": Newest issues first (descending by start time, most recent first)
     *       - "relevance": Most relevant to search query first (only useful with $search parameter)
     *       - "-relevance": Least relevant first (rarely used)
     *     Default: "-started" (newest first)
     *     Example: "-started"
     *
     * @param  int|null  $page
     *     Page number for pagination (1-based indexing). Use with $pageSize to paginate through results.
     *     Default: 1 (first page)
     *     Example: 1, 2, 3, etc.
     *
     * @param  int|null  $pageSize
     *     Number of issues to return per page. Maximum value is 1000.
     *     Default: 50 (if not specified)
     *     Use smaller values (10-50) for faster responses, larger values (100-1000) to reduce API calls.
     *     Example: 50, 100, 500
     *
     * @param  bool|null  $withStatusPageDetails
     *     When true, expands response to include detailed status page information for each issue.
     *     Includes: status page name, URL, visibility settings, branding, subscribers, etc.
     *     Default: false (minimal status page data included)
     *     Set to true when you need full status page context.
     *
     * @param  bool|null  $withMonitorDetails
     *     When true, expands response to include detailed monitor information for each issue.
     *     Includes: monitor configuration, thresholds, notification settings, history, etc.
     *     Default: false (minimal monitor data included)
     *     Set to true when you need to understand monitor configuration or behavior.
     *
     * @param  bool|null  $withAlertDetails
     *     When true, expands response to include detailed alert history for each issue.
     *     Includes: all alerts triggered, notification channels used, delivery status, timestamps, etc.
     *     Default: false (minimal alert data included)
     *     Set to true when you need to audit notifications or understand alert timeline.
     *
     * @param  bool|null  $withComponentDetails
     *     When true, expands response to include detailed component information for each issue.
     *     Includes: component status, dependencies, affected services, historical data, etc.
     *     Default: false (minimal component data included)
     *     Set to true when analyzing infrastructure impact or component health.
     *
     * @return array Returns an array with the following structure:
     *     {
     *       "issues": [
     *         {
     *           "key": "string (unique issue identifier)",
     *           "name": "string (issue title/name)",
     *           "severity": "string (one of: outage, minor_outage, degraded_performance, maintenance, operational, missing_data)",
     *           "state": "string (one of: unresolved, investigating, identified, monitoring, resolved, update)",
     *           "created": "ISO 8601 timestamp (when issue was created)",
     *           "started": "ISO 8601 timestamp (when issue started/became active)",
     *           "ended": "ISO 8601 timestamp|null (when issue was resolved, null if ongoing)",
     *           "assigned_to": "string|null (email address of assigned person)",
     *           "created_by": "string (email address of creator)",
     *           "environment": "string|null (environment key)",
     *           "affected_components": ["string (component keys)"],
     *           "monitors": ["object (associated monitor data)"],
     *           "updates": [
     *             {
     *               "message": "string (update message)",
     *               "state": "string (state at time of update)",
     *               "timestamp": "ISO 8601 timestamp"
     *             }
     *           ],
     *           "alerts": ["object (alert history, included if withAlertDetails=true)"],
     *           "statuspage_details": "object|null (included if withStatusPageDetails=true)",
     *           "monitor_details": "object|null (included if withMonitorDetails=true)",
     *           "component_details": "object|null (included if withComponentDetails=true)"
     *         }
     *       ],
     *       "total": "integer (total number of issues matching filters, across all pages)",
     *       "page": "integer (current page number)",
     *       "pageSize": "integer (issues per page)"
     *     }
     *
     * USAGE EXAMPLES FOR LLM:
     *
     * Example 1: Get all active unresolved issues
     *   issues(state: "unresolved")
     *
     * Example 2: Get critical ongoing incidents from the last 24 hours
     *   issues(state: "unresolved,investigating", severity: "outage,minor_outage", time: "24h")
     *
     * Example 3: Get resolved issues for a specific component with full details
     *   issues(component: "api-gateway", state: "resolved", withComponentDetails: true, withAlertDetails: true)
     *
     * Example 4: Search for database-related issues in production environment
     *   issues(search: "database", env: "production", orderBy: "-started")
     *
     * Example 5: Get paginated list of maintenance windows from last 30 days
     *   issues(severity: "maintenance", time: "30d", page: 1, pageSize: 100)
     *
     * Example 6: Get issues for a specific status page with full context
     *   issues(statuspage: "my-status-page", withStatusPageDetails: true, withMonitorDetails: true)
     *
     * COMMON PATTERNS:
     * - Dashboard view: issues(state: "unresolved,investigating", orderBy: "-started", pageSize: 20)
     * - Incident audit: issues(time: "30d", withAlertDetails: true, withComponentDetails: true)
     * - Status page sync: issues(statuspage: "public-status", state: "unresolved,investigating,monitoring")
     * - Environment health: issues(env: "production", state: "unresolved", severity: "outage,minor_outage,degraded_performance")
     * - Monitor analysis: issues(check: "api-health-check", time: "7d", withMonitorDetails: true)
     */
    #[McpTool]
    public function issues(
        ?string $state = null,
        ?string $severity = null,
        ?string $statuspage = null,
        ?string $group = null,
        ?string $job = null,
        ?string $component = null,
        ?string $check = null,
        ?string $heartbeat = null,
        ?string $site = null,
        ?string $tag = null,
        ?string $type = null,
        ?string $env = null,
        ?string $search = null,
        ?string $time = null,
        ?string $orderBy = null,
        ?int $page = null,
        ?int $pageSize = null,
        ?bool $withStatusPageDetails = null,
        ?bool $withMonitorDetails = null,
        ?bool $withAlertDetails = null,
        ?bool $withComponentDetails = null
    ): array {
        $query_params = array_filter(
            IssuesRequest::from([
                IssuesRequest::state => $state,
                IssuesRequest::severity => $severity,
                IssuesRequest::statuspage => $statuspage,
                IssuesRequest::group => $group,
                IssuesRequest::job => $job,
                IssuesRequest::component => $component,
                IssuesRequest::check => $check,
                IssuesRequest::heartbeat => $heartbeat,
                IssuesRequest::site => $site,
                IssuesRequest::tag => $tag,
                IssuesRequest::type => $type,
                IssuesRequest::env => $env,
                IssuesRequest::search => $search,
                IssuesRequest::time => $time,
                IssuesRequest::orderBy => $orderBy,
                IssuesRequest::page => $page,
                IssuesRequest::pageSize => $pageSize,
                IssuesRequest::withStatusPageDetails => $withStatusPageDetails,
                IssuesRequest::withMonitorDetails => $withMonitorDetails,
                IssuesRequest::withAlertDetails => $withAlertDetails,
                IssuesRequest::withComponentDetails => $withComponentDetails,
            ])->toArray()
        );

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