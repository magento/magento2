//  Copyright © Magento, Inc. All rights reserved.
//  See COPYING.txt for license details.
    example.com:8080

    by ips:
    "127.0.0.1";
    "192.168.0.1";
    "127.0.0.2";

    design exceptions:
    if (req.http.user-agent ~ "(?pattern)?i") {
        hash_data("value_for_pattern");
    }

    ssl offloaded header:
    X-Forwarded-Proto: https

    grace:
    120

    normalize parameters:
    # strip normalized parameters from query string
    set req.url = regsuball(req.url, "((\?)|&)(gclid|gclsrc|utm_content|utm_term|utm_campaign|utm_medium|utm_source|_ga)=[^&]*", "");
    set req.url = regsub(req.url, "(\?&|\?|&)$", "");
