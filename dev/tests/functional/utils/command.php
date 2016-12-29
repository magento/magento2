<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['command'])) {
    $command = urldecode($_GET['command']);
    exec('/usr/local/Cellar/php70/7.0.10_1/bin/php -f ../../../../bin/magento ' . $command);
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
