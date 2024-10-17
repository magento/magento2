<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Console\CommandLocator;
use Magento\SampleData\Console\CommandList;

if (PHP_SAPI == 'cli') {
    CommandLocator::register(CommandList::class);
}
