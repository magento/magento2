<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
