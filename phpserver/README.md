PHP Built-in webserver
======================

php has a Built-in webserver since version 5.4
https://secure.php.net/manual/en/features.commandline.webserver.php

As many applications and frameworks rely on rewrites on webserver side,
the same as Magento does, it offers an argument for a router script.
Either the script returns false which means, it should try to deliver the file
as usual via file system lookup, or it executes the specific php scripts via include.

Example:
requests to `/static/frontend/Magento/blank/en_US/mage/calendar.css` should deliver the file if it exists, or execute `/static.php` if not.

Without a router script, that is not possible via the php built-in server.

### How to use it

example usage: ```php -S 127.0.0.41:8082 -t ./pub/ ./phpserver/router.php```

### What exactly the script does

first we have an low level `$debug` closure, for the case you need to debug execution.

If the request path starts with index.php, get.php, static.php, we return to normal request flow.
If we notice a favicon.ico request, the same.

Then rewrite paths for `pub/errors/default/` by removing the `pub/` part. (was at least needed for older versions)

Request starting with `media/`, `opt/`, `static/` test if the file exists.
If Yes, then handle it, if not "forward" `static` to `static.php` and `media` to `get.php`

If none of the rules matched, return 404.
You may instead include the index.php, if 404 should be handled by Magento or you want
urls without `index.php/`.
