**Setup** provides interfaces that should be used or implemented by Setup data and schema installs, upgrades and uninstalls.

Implement `InstallSchemaInterface` and/or `UpgradeSchemaInterface` for DB schema install and/or upgrade.
Implement `InstallDataInterface` and/or `UpgradeDataInterface` for DB data install and/or upgrade.
Implement `UninstallInterface` for handling data removal during module uninstall.

Setup application provides concrete implementation of a module context and setup DB/schema resources, so they can be used to determine current state of the module and get access to DB resource.
