<?php

namespace App\Http\Requests;

use App\Helpers\DataModel;
use Zerotoprod\DataModel\Describe;

readonly class IssuesRequest
{
    use DataModel;

    /** @see $state */
    public const string state = 'state';
    /** Filter by issue state(s). Multiple values supported */
    #[Describe(['nullable'])]
    public ?string $state;

    /** @see $severity */
    public const string severity = 'severity';
    /** Filter by severity level(s). Multiple values supported */
    #[Describe(['nullable'])]
    public ?string $severity;

    /** @see $statuspage */
    public const string statuspage = 'statuspage';
    /** Filter by status page key */
    #[Describe(['nullable'])]
    public ?string $statuspage;

    /** @see $group */
    public const string group = 'group';
    /** Filter by monitor group key(s) */
    #[Describe(['nullable'])]
    public ?string $group;

    /** @see $job */
    public const string job = 'job';
    /** Filter by monitor key(s) */
    #[Describe(['nullable'])]
    public ?string $job;

    /** @see $component */
    public const string component = 'component';
    /** Filter by status page component key(s) */
    #[Describe(['nullable'])]
    public ?string $component;

    /** @see $check */
    public const string check = 'check';
    /** Filter by check monitor key(s) */
    #[Describe(['nullable'])]
    public ?string $check;

    /** @see $heartbeat */
    public const string heartbeat = 'heartbeat';
    /** Filter by heartbeat monitor key(s) */
    #[Describe(['nullable'])]
    public ?string $heartbeat;

    /** @see $site */
    public const string site = 'site';
    /** Filter by site monitor key(s) */
    #[Describe(['nullable'])]
    public ?string $site;

    /** @see $tag */
    public const string tag = 'tag';
    /** Filter by monitor tag(s) */
    #[Describe(['nullable'])]
    public ?string $tag;

    /** @see $type */
    public const string type = 'type';
    /** Filter by monitor type(s) */
    #[Describe(['nullable'])]
    public ?string $type;

    /** @see $env */
    public const string env = 'env';
    /** Filter by environment key */
    #[Describe(['nullable'])]
    public ?string $env;

    /** @see $search */
    public const string search = 'search';
    /** Search in issue names and associated monitor names/keys/codes */
    #[Describe(['nullable'])]
    public ?string $search;

    /** @see $time */
    public const string time = 'time';
    /** Time range filter (e.g., "24h", "7d", "30d") */
    #[Describe(['nullable'])]
    public ?string $time;

    /** @see $orderBy */
    public const string orderBy = 'orderBy';
    /** Sort order: "started", "-started", "relevance", "-relevance" */
    #[Describe(['nullable'])]
    public ?string $orderBy;

    /** @see $page */
    public const string page = 'page';
    /** Page number for pagination */
    #[Describe(['nullable'])]
    public ?int $page;

    /** @see $pageSize */
    public const string pageSize = 'pageSize';
    /** Number of results per page (max 1000) */
    #[Describe(['nullable'])]
    public ?int $pageSize;

    /** @see $withStatusPageDetails */
    public const string withStatusPageDetails = 'withStatusPageDetails';
    /** Include detailed status page information */
    #[Describe(['nullable', 'cast' => [self::class, 'toBool']])]
    public ?bool $withStatusPageDetails;

    /** @see $withMonitorDetails */
    public const string withMonitorDetails = 'withMonitorDetails';
    /** Include detailed monitor information */
    #[Describe(['nullable', [self::class, 'toBool']])]
    public ?bool $withMonitorDetails;

    /** @see $withAlertDetails */
    public const string withAlertDetails = 'withAlertDetails';
    /** Include alert history details */
    #[Describe(['nullable', [self::class, 'toBool']])]
    public ?bool $withAlertDetails;

    /** @see $withComponentDetails */
    public const string withComponentDetails = 'withComponentDetails';
    /** Include status page component details */
    #[Describe(['nullable', [self::class, 'toBool']])]
    public ?bool $withComponentDetails;

    public static function toBool(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $value
            ? 'true'
            : 'false';
    }
}