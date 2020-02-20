<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron\ConsumersRunner;

/**
 * @param $pid
 * @return bool|int
 */
function posix_getpgid($pid)
{
    if ($pid === 11111) {
        return 22222;
    }

    return false;
}

/**
 * @param $command
 * @param array|null $output
 * @param null $return_var
 */
function exec($command, array &$output = null, &$return_var = null)
{
    $output = ['PID TTY TIME CMD'];
    $return_var = 1;

    if ($command === 'ps -p 11111') {
        $output[] = ['11111 ?? 25:49.42 /php'];
        $return_var = 0;
    }
}
