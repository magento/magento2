<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register(\Magento\SampleData\Console\CommandList::class);
}
