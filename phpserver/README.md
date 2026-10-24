# PHP Built-in webserver

PHP has had a [built-in web sever](https://secure.php.net/manual/en/features.commandline.webserver.php) since version 5.4.

PHP's web server provides a router script for use with server rewrites. Magento, like many other applications and frameworks, requires server rewrites. The router script either:

- The web server executes the requested PHP script using a server-side include
- Returns `false`, which means the web server returns the file using file system lookup

Example:
requests to `/static/frontend/Magento/blank/en_US/mage/calendar.css` should deliver the file if it exists, or execute `/static.php` if not.

Without a router script, that is not possible via the php built-in server.

## How to install Magento

Please read how to install Magento using the [command line](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/advanced.html). An example follows:

```php
php bin/magento setup:install --base-url=http://127.0.0.1:8082 \
--db-host=localhost --db-name=magento --db-user=magento --db-password=magento \
--admin-firstname=Magento --admin-lastname=User --admin-email=user@example.com \
--admin-user=admin --admin-password=admin123 --language=en_US \
--currency=USD --timezone=America/Chicago --use-rewrites=1 \
--search-engine=elasticsearch8 --elasticsearch-host=es-host.example.com --elasticsearch-port=9200
```

Note: By default, Magento creates a random Admin URI for you. Make sure to write this value down because it's how you access the Magento Admin later. For example: `http://127.0.0.1:8082/index.php/admin_1vpn01`.

For more information about the installation process using the CLI, you can consult the dedicated documentation that can found in [the developer documentation](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/composer.html).

### How to run Magento

Example usage:

```shell
php -S 127.0.0.1:8082 -t ./pub/ ./phpserver/router.php
```

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
