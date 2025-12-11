# Magento_AsyncConfig module

This module enables admin config save asynchronously, which saves configuration in a queue, and processes it in a first-in-first-out basis.

AsyncConfig values:

-  `0` — (_Default value_) Disable the module and use the standard synchronous configuration save.  
-  `1` — Enable the module for asynchronous config save.

To enable the module, set the `config/async` variable in the `env.php` file. For example:

```php
<?php
      'config' => [
               'async' => 1
       ]
```

Alternatively, you can set the variable using the command-line interface:

```bash
bin/magento setup:config:set --config-async 1
```
