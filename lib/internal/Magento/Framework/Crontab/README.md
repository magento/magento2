Library for working with crontab

The library has the next interfaces:

* CrontabManagerInterface
* TasksProviderInterface

*CrontabManagerInterface* provides working with crontab:

* *getTasks* - get list of Magento cron tasks from crontab
* *saveTasks* - save Magento cron tasks to crontab
* *removeTasks* - remove Magento cron tasks from crontab

*TasksProviderInterface* has only one method *getTasks*. This interface provides transportation the list of tasks from DI
