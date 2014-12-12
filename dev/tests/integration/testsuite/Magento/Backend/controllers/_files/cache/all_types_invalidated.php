<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $cacheTypeList \Magento\Framework\App\Cache\TypeListInterface */
$cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Framework\App\Cache\TypeListInterface'
);
$cacheTypeList->invalidate(array_keys($cacheTypeList->getTypes()));
