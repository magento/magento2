<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;

require __DIR__ . '/autoload.php';

$dirSearch = new DirSearch(new ComponentRegistrar(), new ReadFactory(new DriverPool()));
\Magento\Framework\App\Utility\Files::setInstance(
    new \Magento\Framework\App\Utility\Files(new ComponentRegistrar(), $dirSearch)
);
