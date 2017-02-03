vcl 4.0;

import std;
# The minimal Varnish version is 4.0
# For SSL offloading, pass the following header in your proxy server or load balancer: '/* {{ ssl_offloaded_header }} */: https'

backend default {
    .host = "/* {{ host }} */";
    .port = "/* {{ port }} */";
    .first_byte_timeout = 600s;
    .probe = {
        .url = "/pub/health_check.php";
        .timeout = 2s;
        .interval = 5s;
        .window = 10;
        .threshold = 5;
   }
}

acl purge {
/* {{ ips }} */
}

sub vcl_recv {
    if (req.method == "PURGE") {
        if (client.ip !~ purge) {
            return (synth(405, "Method not allowed"));
        }
        if (!req.http.X-Magento-Tags-Pattern) {
            return (synth(400, "X-Magento-Tags-Pattern header required"));
        }
        ban("obj.http.X-Magento-Tags ~ " + req.http.X-Magento-Tags-Pattern);
        return (synth(200, "Purged"));
    }

    if (req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE") {
          /* Non-RFC2616 or CONNECT which is weird. */
          return (pipe);
    }

    # We only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Bypass shopping cart, checkout and search requests
    if (req.url ~ "/checkout" || req.url ~ "/catalogsearch") {
        return (pass);
    }

    # Bypass health check requests
    if (req.url ~ "/pub/health_check.php") {
        return (pass);
    }

    # Set initial grace period usage status
    set req.http.grace = "none";

    # normalize url in case of leading HTTP scheme and domain
    set req.url = regsub(req.url, "^http[s]?://", "");

    # collect all cookies
    std.collect(req.http.Cookie);

    # Compression filter. See https://www.varnish-cache.org/trac/wiki/FAQ/Compression
    if (req.http.Accept-Encoding) {
        if (req.url ~ "\.(jpg|jpeg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|flv)$") {
            # No point in compressing these
            unset req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate" && req.http.user-agent !~ "MSIE") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            # unkown algorithm
            unset req.http.Accept-Encoding;
        }
    }

    # Remove Google gclid parameters to minimize the cache objects
    set req.url = regsuball(req.url,"\?gclid=[^&]+$",""); # strips when QS = "?gclid=AAA"
    set req.url = regsuball(req.url,"\?gclid=[^&]+&","?"); # strips when QS = "?gclid=AAA&foo=bar"
    set req.url = regsuball(req.url,"&gclid=[^&]+",""); # strips when QS = "?foo=bar&gclid=AAA" or QS = "?foo=bar&gclid=AAA&bar=baz"

    # static files are always cacheable. remove SSL flag and cookie
        if (req.url ~ "^/(pub/)?(media|static)/.*\.(ico|html|css|js|jpg|jpeg|png|gif|tiff|bmp|mp3|ogg|svg|swf|woff|woff2|eot|ttf|otf)$") {
        unset req.http.Https;
        unset req.http./* {{ ssl_offloaded_header }} */;
        unset req.http.Cookie;
    }

    return (hash);
}

sub vcl_hash {
    if (req.http.cookie ~ "X-Magento-Vary=") {
        hash_data(regsub(req.http.cookie, "^.*?X-Magento-Vary=([^;]+);*.*$", "\1"));
    }

    # For multi site configurations to not cache each other's content
    if (req.http.host) {
        hash_data(req.http.host);
    } else {
        hash_data(server.ip);
    }

    # To make sure http users don't see ssl warning
    if (req.http./* {{ ssl_offloaded_header }} */) {
        hash_data(req.http./* {{ ssl_offloaded_header }} */);
    }
    /* {{ design_exceptions_code }} */
}

sub vcl_backend_response {

    set beresp.grace = 3d;

    if (beresp.http.content-type ~ "text") {
        set beresp.do_esi = true;
    }

    if (bereq.url ~ "\.js$" || beresp.http.content-type ~ "text") {
        set beresp.do_gzip = true;
    }

    # cache only successfully responses and 404s
    if (beresp.status != 200 && beresp.status != 404) {
        set beresp.ttl = 0s;
        set beresp.uncacheable = true;
        return (deliver);
    } elsif (beresp.http.Cache-Control ~ "private") {
        set beresp.uncacheable = true;
        set beresp.ttl = 86400s;
        return (deliver);
    }

    if (beresp.http.X-Magento-Debug) {
        set beresp.http.X-Magento-Cache-Control = beresp.http.Cache-Control;
    }

    # validate if we need to cache it and prevent from setting cookie
    # images, css and js are cacheable by default so we have to remove cookie also
    if (beresp.ttl > 0s && (bereq.method == "GET" || bereq.method == "HEAD")) {
        unset beresp.http.set-cookie;
        if (bereq.url !~ "\.(ico|css|js|jpg|jpeg|png|gif|tiff|bmp|gz|tgz|bz2|tbz|mp3|ogg|svg|swf|woff|woff2|eot|ttf|otf)(\?|$)") {
            set beresp.http.Pragma = "no-cache";
            set beresp.http.Expires = "-1";
            set beresp.http.Cache-Control = "no-store, no-cache, must-revalidate, max-age=0";
        }
    }

   # If page is not cacheable then bypass varnish for 2 minutes as Hit-For-Pass
   if (beresp.ttl <= 0s ||
        beresp.http.Surrogate-control ~ "no-store" ||
        (!beresp.http.Surrogate-Control && beresp.http.Vary == "*")) {
        # Mark as Hit-For-Pass for the next 2 minutes
        set beresp.ttl = 120s;
        set beresp.uncacheable = true;
    }
    return (deliver);
}

sub vcl_deliver {
    if (resp.http.X-Magento-Debug) {
        if (resp.http.x-varnish ~ " ") {
            set resp.http.X-Magento-Cache-Debug = "HIT";
            set resp.http.Grace = req.http.grace;
        } else {
            set resp.http.X-Magento-Cache-Debug = "MISS";
        }
    } else {
        unset resp.http.Age;
    }

    unset resp.http.X-Magento-Debug;
    unset resp.http.X-Magento-Tags;
    unset resp.http.X-Powered-By;
    unset resp.http.Server;
    unset resp.http.X-Varnish;
    unset resp.http.Via;
    unset resp.http.Link;
}

sub vcl_hit {
    if (obj.ttl >= 0s) {
        # Hit within TTL period
        return (deliver);
    }
    if (std.healthy(req.backend_hint)) {
        if (obj.ttl + /* {{ grace_period }} */s > 0s) {
            # Hit after TTL expiration, but within grace period
            set req.http.grace = "normal (healthy server)";
            return (deliver);
        } else {
            # Hit after TTL and grace expiration
            return (fetch);
        }
    } else {
        # server is not healthy, retrieve from cache
        set req.http.grace = "unlimited (unhealthy server)";
        return (deliver);
    }
}
