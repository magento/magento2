<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

/**
 * @param $func
 * @return bool
 */
function function_exists($func)
{
    return $func !== 'pcntl_fork';
}
