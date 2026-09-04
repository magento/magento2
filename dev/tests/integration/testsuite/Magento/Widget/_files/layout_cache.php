<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Framework\App\Cache\Type\Layout $layoutCache */
$layoutCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Framework\App\Cache\Type\Layout::class);
$layoutCache->save('fixture layout cache data', 'LAYOUT_CACHE_FIXTURE');
