<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['command'])) {
    $php = PHP_BINARY ?: (PHP_BINDIR ? PHP_BINDIR . '/php' : 'php');
    $command = urldecode($_GET['command']);
    exec($php . ' -f ../../../../bin/magento ' . $command);
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
