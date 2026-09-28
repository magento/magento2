<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register(\Magento\Deploy\Console\CommandList::class);
}
