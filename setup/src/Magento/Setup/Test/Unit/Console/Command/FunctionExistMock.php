<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Console\Command;

/**
 * @param $func
 * @return bool
 */
function function_exists($func)
{
    return $func !== 'pcntl_fork';
}
