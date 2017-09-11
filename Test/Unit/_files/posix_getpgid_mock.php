<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron\ConsumersRunner;

function posix_getpgid($pid)
{
    if ($pid === 11111) {
        return 22222;
    }

    return false;
}
