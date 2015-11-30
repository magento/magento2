TESTING<br/>

to enable configuration you have to run these commands:<br/>
```cd /etc/nginx/sites-enabled/```<br/>
```ln -s /etc/nginx/sites-avalable/default.conf ./default.conf```<br/>
```ln -s /etc/nginx/sites-avalable/magento2.conf ./magento2.conf```<br/>
```nginx -t```<br/>
if no errors detected, then<br/>
```service nginx restart```

<br/>
for ssl configuration in nginx.conf you must: <br/>
1 - open ```cd /etc/ssl/certs``` <br/>
2 - generate dhparam file ```openssl dhparam -out dhparams.pem 2048``` <br/>
3 - enable in nginx.conf ```ssl_dhparam /etc/ssl/certs/dhparams.pem;``` <br/>

```conf.d/assets.conf``` => settings for any static assets<br/>
```conf.d/error_page.conf``` => configure custom error pages<br/>
```conf.d/extra_protect.conf``` => protecting everything<br/>
```conf.d/hhvm.conf``` => hhvm vs php-fpm port/route mapping<br/>
```conf.d/maintenance.conf``` => global maintenance<br/>
```conf.d/multishop.conf``` => settings for multistore code<br/>
```conf.d/pagespeed.conf``` => pagespeed module settings<br/>
```conf.d/php_backend.conf``` => global settings for php execution<br/>
```conf.d/setup.conf``` => magento web setup/update (before web installation create dummy ```admin``` cookie as httponly)<br/>
```conf.d/spider.conf``` => bad user agents mapping<br/>
```conf.d/status.conf``` => nginx/php-fpm status locations<br/>

```www/default.conf``` => catch non-existent server name<br/>
```www/magento2.conf``` => magento virtual host/server configuration file<br/>

```fastcgi_params``` => global fastcgi parameters<br/>
```nginx.conf``` => main nginx configuration file<br/>
```port.conf``` => configure http port<br/>
