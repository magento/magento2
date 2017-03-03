<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['command'])) {
    $command = urldecode($_GET['command']);
    exec('../../../../bin/magento ' . $command);
} else {
    throw new \Exception("Command GET parameter is not set.");
}
