<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
if (PHP_SAPI === 'cli') {
    \Magento\Framework\Console\CommandLocator::register(\Magento\Backend\Console\CommandList::class);
}
