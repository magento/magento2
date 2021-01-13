# Magento_Cron module

This module enables scheduling of cron jobs. 
Other modules can add cron jobs by including crontab.xml in their `etc` directory.
This module also allows administrators to tune cron options in Magento Admin.

## Installation

The Magento_Cron module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Additional information

### Configure and run cron
To setup cron jobs see: [Set up cron jobs](https://devdocs.magento.com/cloud/configure/setup-cron-jobs.html).
To configure and run cron jobs see: [Configure and run cron](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html).

### Create custom cron jobs
Extension developers can interact with the Magento_Cron module to create custom cron jobs and cron groups. 
1. Create a class to run cron:
   - create directory <module_dir>/Cron
   - create class file in that directory 
2. Create a `crontab.xml` in the <module_dir>/etc.

For more information about creating custom cron jobs, see:
[Custom cron job and cron group reference](https://devdocs.magento.com/guides/v2.4/config-guide/cron/custom-cron-ref.html)
[Configure custom cron job and cron group](https://devdocs.magento.com/guides/v2.4/config-guide/cron/custom-cron-tut.html).

### Logging
By default, the cron information can be found at <install_directory>/var/log/cron.log

### Console commands
 - `bin/magento cron:install [--force]` - create the Magento crontab
 - `bin/magento cron:remove [--force]` - remove the Magento crontab
 - `bin/magento cron:run [--group="<cron group name>"]` - run cron jobs
