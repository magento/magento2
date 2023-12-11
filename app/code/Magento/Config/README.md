# Magento_Config module

The Config module is designed to implement system configuration functionality.
It provides mechanisms to add, edit, store and retrieve the configuration data for each scope (there can be a default scope as well as scopes for each website and store).

Modules can add items to be configured on the system configuration page by creating system.xml files in their etc/adminhtml directories. These system.xml files get merged to populate the forms in the config page.
