<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['website_code'])) {
    $websiteCode = urldecode($_GET['website_code']);
    exec('./website.sh ' . $websiteCode);
} else {
    throw new \Exception("website_code GET parameter is not set.");
}
