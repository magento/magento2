// @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
    example.com:8080

    by ips:
    "127.0.0.1";
    "192.168.0.1";

    if (req.http.user-agent ~ "(?pattern)?i") {
        hash_data("value_for_pattern");
    }