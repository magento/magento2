PHP Built-in webserver
======================

PHP has had a <a href="https://secure.php.net/manual/en/features.commandline.webserver.php" target="_blank">built-in web sever</a> since version 5.4.

PHP's web server provides a router script for use with server rewrites. Magento, like many other applications and frameworks, requires server rewrites. The router script either:
- The web server executes the requested PHP script using a server-side include
- Returns `false`, which means the web server returns the file using file system lookup

Example:
requests to `/static/frontend/Magento/blank/en_US/mage/calendar.css` should deliver the file if it exists, or execute `/static.php` if not.

Without a router script, that is not possible via the php built-in server.

### How to install Magento

Magento's web-based Setup Wizard runs from the `setup` subdirectory, which PHP's built-in web server cannot route. Therefore, you must install Magento using the <a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/cli/install-cli.html" target="_blank">command line</a>. An example follows:

```
php bin/magento setup:install --base-url=http://127.0.0.1:8082 
--db-host=localhost --db-name=magento --db-user=magento --db-password=magento
--admin-firstname=Magento --admin-lastname=User --admin-email=user@example.com
--admin-user=admin --admin-password=admin123 --language=en_US
--currency=USD --timezone=America/Chicago --use-rewrites=0
```

It's important to note that the router is not able to work with rewrite urls, that's why the flag `use-rewrites` is set to `0`.

Notes:
- You must use `--use-rewrites=0` because the web server cannot rewrite URLs
- By default, Magento creates a random Admin URI for you. Make sure to write this value down because it's how you access the Magento Admin later. For example : ```http://127.0.0.1:8082/index.php/admin_1vpn01```.

For more informations about the installation process using the CLI, you can consult the dedicated documentation that can found in [the developer documentation](https://github.com/magento/devdocs/blob/develop/guides/v2.0/install-gde/install/cli/install-cli-install.md).

### How to run Magento

Example usage: ```php -S 127.0.0.1:8082 -t ./pub/ ./phpserver/router.php```

### What exactly the script does

The `$debug` option provides low-level logging for debugging purposes.

Forwarding rules:
- Any request for `favicon.ico` or for any path that starts with `index.php`, `get.php`, `static.php` are processed normally.
- Requests for the path `pub/errors/default` are rewritten as `errors/default`. This is provided for compatibility with older versions.
- Files under request paths `media`, `opt`, or `static` are tested; if the file exists, the file is served. If the file does not exist, `static` files are forwarded to `static.php` and `media` files are forwarded to `get.php` ((How about `opt`?))
- If no rules are matched, return a 404 (Not Found).

Then rewrite paths for `pub/errors/default/` by removing the `pub/` part. (was at least needed for older versions)

Request starting with `media/`, `opt/`, `static/` test if the file exists. If Yes, then handle it, if not "forward" `static` to `static.php` and `media` to `get.php`

If none of the rules matched, return 404. You may instead include the index.php, if 404 should be handled by Magento or you want urls without `index.php/`.

### How to access to the admin dashboard

When the installation is finished, you can access Magento as follows:
- Storefront: `<your Magento base URL>`
- Magento Admin: `<your Magento base URL>/<admin URI>`

