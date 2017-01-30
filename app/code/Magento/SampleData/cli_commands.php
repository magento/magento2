<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register('Magento\SampleData\Console\CommandList');
}
