# ApplicationPerformanceMonitorNewRelic module

This module monitors the performance of the application in New Relic.

To use this module, you need a New Relic account and your environment must be configured to use that account.
For general New Relic PHP configuration information, see [PHP agent configuration](https://docs.newrelic.com/docs/apm/agents/php-agent/configuration/php-agent-configuration/).

To configure this module, edit `app/etc/env.php` and add these lines:

```php
'application' => [
    'performance_monitor' => [
        'newrelic_output_enable' => 1,
        'newrelic_output_verbose' => 0,
    ]
]
```

Use 0 or 1 as the value to enable or disable.
`newrelic_output_enable` defaults to 1, and `newrelic_output_verbose` defaults to 0.

The option `newrelic_output_enable` enables outputting performance metrics to New Relic.
The option `newrelic_output_verbose` adds additional metrics.

See README.md in ApplicationPerformanceMonitor for details about the metrics.
