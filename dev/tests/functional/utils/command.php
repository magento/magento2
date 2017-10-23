<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['command'])) {
    $php = PHP_BINARY ?: (PHP_BINDIR ? PHP_BINDIR . '/php' : 'php');
    $command = urldecode($_GET['command']);
    exec(escapeCommand($php . ' -f ../../../../bin/magento ' . $command));
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}

/**
 * Returns escaped command.
 *
 * @param string $command
 * @return string
 */
function escapeCommand($command)
{
    $escapeExceptions = [
        '> /dev/null &' => '--dev-null-amp--'
    ];

    $command = escapeshellcmd(
        str_replace(array_keys($escapeExceptions), array_values($escapeExceptions), $command)
    );

    return str_replace(array_values($escapeExceptions), array_keys($escapeExceptions), $command);
}
