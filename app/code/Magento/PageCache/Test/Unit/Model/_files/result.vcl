//  Copyright 2014 Adobe
//  All Rights Reserved.
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
