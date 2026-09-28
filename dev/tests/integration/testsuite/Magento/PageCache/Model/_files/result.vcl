//  Copyright 2017 Adobe
//  All Rights Reserved.
    example.com:8080

    by ips:
    "127.0.0.1";
    "192.168.0.1";
    "127.0.0.2";

    if (req.http.user-agent ~ "(?i)firefox") {
        hash_data("Magento/blank");
    }
