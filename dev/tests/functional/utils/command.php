<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$commandList = [
    'cache:flush',
    'cache:disable',
    'cache:enable',
    'setup:static-content:deploy',
    'cron:run',
];

if (isset($_GET['command'])) {
    $command = urldecode($_GET['command']);
    if (!strpos($command, '&&') && !strpos($command, ';')) {
        if (in_array(explode(' ', $command)[0], $commandList)) {
            exec('php -f ../../../../bin/magento ' . $command);
        }
    }
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
