<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['command'])) {
    $command = urldecode($_GET['command']);
    exec('php -f ../../../../bin/magento ' . $command);
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
