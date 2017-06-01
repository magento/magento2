PHP Built-in webserver
======================

php has a Built-in webserver since version 5.4 https://secure.php.net/manual/en/features.commandline.webserver.php

As many applications and frameworks rely on rewrites on webserver side, the same as Magento does, it offers an argument for a router script. Either the script returns false which means, it should try to deliver the file as usual via file system lookup, or it executes the specific php scripts via include.

Example:
requests to `/static/frontend/Magento/blank/en_US/mage/calendar.css` should deliver the file if it exists, or execute `/static.php` if not.

Without a router script, that is not possible via the php built-in server.

### How to install Magento

Because the router can not route the `setup` folder, it's necessary to install Magento manually without the web wizard. You can process the installation for your application using the following command line: 

```
php bin/magento setup:install --base-url=http://127.0.0.1:8082 
--db-host=localhost --db-name=magento --db-user=magento --db-password=magento
--admin-firstname=Magento --admin-lastname=User --admin-email=user@example.com
--admin-user=admin --admin-password=admin123 --language=en_US
--currency=USD --timezone=America/Chicago --use-rewrites=0
```

It's important to note that the router is not able to work with rewrite urls, that's why the flag `use-rewrites` is set to `0`.

At the end of the installation process, don't forget to note the admin uri. That will let you access to the admin panel. For example : ```http://127.0.0.1:8082/index.php/admin_1vpn01```.

For more informations about the installation process using the CLI, you can consult the dedicated documentation that can found in [the developer documentation](https://github.com/magento/devdocs/blob/develop/guides/v2.0/install-gde/install/cli/install-cli-install.md).

### How to run Magento

Example usage: ```php -S 127.0.0.1:8082 -t ./pub/ ./phpserver/router.php```

### What exactly the script does

first we have an low level `$debug` closure, for the case you need to debug execution.

If the request path starts with index.php, get.php, static.php, we return to normal request flow. If we notice a favicon.ico request, the same.

Then rewrite paths for `pub/errors/default/` by removing the `pub/` part. (was at least needed for older versions)

Request starting with `media/`, `opt/`, `static/` test if the file exists. If Yes, then handle it, if not "forward" `static` to `static.php` and `media` to `get.php`

If none of the rules matched, return 404. You may instead include the index.php, if 404 should be handled by Magento or you want urls without `index.php/`.

### How to access to the admin dashboard

At the end of the installation process, it's necessary to get the admin uri.
