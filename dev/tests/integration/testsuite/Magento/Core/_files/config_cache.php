<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\App\Cache\Type\Config $layoutCache */
$layoutCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\App\Cache\Type\Config');
$layoutCache->save('fixture config cache data', 'CONFIG_CACHE_FIXTURE');
