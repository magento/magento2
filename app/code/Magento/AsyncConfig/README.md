# AsyncConfig

The _AsyncConfig_ module enables admin config save asynchronously, which saves configuration in a queue, and processes it in a first-in-first-out basis.

AsyncConfig values:

-  `0` — (_Default value_) Disable the AsyncConfig module and use the standard synchronous configuration save.  
-  `1` — Enable the AsyncConfig module for asynchronous config save.

To enable AsyncConfig, set the `config/async` variable in the `env.php` file. For example:

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
