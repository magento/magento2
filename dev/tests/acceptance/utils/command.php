<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_POST['command'])) {
    $command = urldecode($_POST['command']);
    $php = PHP_BINARY ?: (PHP_BINDIR ? PHP_BINDIR . '/php' : 'php');
    $valid = validateCommand($command);
    if ($valid) {
        exec(escapeCommand($php . ' -f ../../../../bin/magento ' . $command) . " 2>&1", $output, $exitCode);
        if ($exitCode == 0) {
            http_response_code(202);
        } else {
            http_response_code(500);
        }
        echo implode("\n", $output);
    } else {
        http_response_code(403);
        echo "Given command not found valid in Magento CLI Command list.";
    }
} else {
    http_response_code(412);
    echo("Command parameter is not set.");
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

/**
 * Checks magento list of CLI commands for given $command. Does not check command parameters, just base command.
 * @param string $command
 * @return bool
 */
function validateCommand($command)
{
    $php = PHP_BINARY ?: (PHP_BINDIR ? PHP_BINDIR . '/php' : 'php');
    exec($php . ' -f ../../../../bin/magento list', $commandList);
    // Trim list of commands after first whitespace
    $commandList = array_map("trimAfterWhitespace", $commandList);
    return in_array(trimAfterWhitespace($command), $commandList);
}

/**
 * Returns given string trimmed of everything after the first found whitespace.
 * @param string $string
 * @return string
 */
function trimAfterWhitespace($string)
{
    return strtok($string, ' ');
}
