<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\App\Cache\Type\Layout $layoutCache */
$layoutCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\App\Cache\Type\Layout');
$layoutCache->save('fixture layout cache data', 'LAYOUT_CACHE_FIXTURE');
