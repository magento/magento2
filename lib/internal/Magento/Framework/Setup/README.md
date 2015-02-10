**Setup** provides interfaces that should be used or implemented by Setup data and schema installs/upgrades.
 
Implement `InstallSchemaInterface` and/or `UpgradeSchemaInterface` for DB schema install and/or upgrade.
Implement `InstallDataInterface` and/or `UpgradeDataInterface` for DB data install and/or upgrade.

Setup application provides concrete implementation of a module context and setup DB/schema resources.
Additionally, you may implement `ModuleSchemaResourceInterface` or `ModuleDataResourceInterface`, if your module
requires custom setup resources.
