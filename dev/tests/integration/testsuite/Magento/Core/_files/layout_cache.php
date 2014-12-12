<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var \Magento\Framework\App\Cache\Type\Layout $layoutCache */
$layoutCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\App\Cache\Type\Layout');
$layoutCache->save('fixture layout cache data', 'LAYOUT_CACHE_FIXTURE');
