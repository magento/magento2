**ApplicationPerformanceMonitor**

Monitors the Performance of the Application

To configure, edit app/etc/env.php
Add these lines.

```
'application' => [
    'performance_monitor' => [
        'logger_output_enable' => 1,
        'logger_output_verbose' => 0,
    ]
]
```

Use 0 or 1 as the value to enable or disable.
Both `logger_output_enable` and `logger_output_verbose` default to 0.

The option `logger_output_enable` enables outputting performance metrics to the logger using `debug` method of logger.
The option `logger_output_verbose` adds additional metrics.

Example output in log file without verbose:
```
[2023-10-04T20:48:23.727037+00:00] report.ERROR: "Profile information": {
        "applicationClass":     "Magento\ApplicationServer\App\Application\Interceptor",
        "applicationServer":    "1",
        "threadPreviousRequestCount":   "73",
        "memoryUsageAfter":     "240 MB",
        "memoryUsageAfterComparedToPrevious":   "0 B",
        "memoryUsageDelta":     "118 KB",
        "peakMemoryUsageAfter": "243 MB",
        "peakMemoryUsageDelta": "0 B",
        "wallTimeElapsed":      "0 s"
}
```

Example output in log file with verbose:
```
[2023-10-04T20:55:31.174304+00:00] report.ERROR: "Profile information": {
        "applicationClass":     "Magento\ApplicationServer\App\Application\Interceptor",
        "applicationServer":    "1",
        "threadPreviousRequestCount":   "42",
        "memoryUsageBefore":    "239568640 B",
        "memoryUsageAfter":     "239686808 B",
        "memoryUsageAfterComparedToPrevious":   "0 B",
        "memoryUsageDelta":     "118168 B",
        "peakMemoryUsageBefore":        "243053632 B",
        "peakMemoryUsageAfter": "243053632 B",
        "peakMemoryUsageDelta": "0 B",
        "wallTimeBefore":       "2023-10-04T20:55:31.170300",
        "wallTimeAfter":        "2023-10-04T20:55:31.174200",
        "wallTimeElapsed":      "0.0038700103759766 s",
        "userTimeBefore":       "3.771626 s",
        "userTimeAfter":        "3.771626 s",
        "userTimeElapsed":      "0 s",
        "systemTimeBefore":     "0.095585 s",
        "systemTimeAfter":      "0.099126 s",
        "systemTimeElapsed":    "0.003541 s"
}
```

The additional options `newrelic_output_enable` and `newrelic_output_verbose` are only used if ApplicationPerformanceMonitorNewRelic module is also installed and enabled.
See README.md in ApplicationPerformanceMonitorNewRelic for more details on that.
