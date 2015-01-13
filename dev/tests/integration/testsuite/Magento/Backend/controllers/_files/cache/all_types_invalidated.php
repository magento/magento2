<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $cacheTypeList \Magento\Framework\App\Cache\TypeListInterface */
$cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Framework\App\Cache\TypeListInterface'
);
$cacheTypeList->invalidate(array_keys($cacheTypeList->getTypes()));
