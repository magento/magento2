<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register('Magento\SampleData\Console\CommandList');
}
