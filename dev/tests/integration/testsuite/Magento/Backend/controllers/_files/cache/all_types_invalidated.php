<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/** @var $cacheTypeList \Magento\Framework\App\Cache\TypeListInterface */
$cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Framework\App\Cache\TypeListInterface::class
);
$cacheTypeList->invalidate(array_keys($cacheTypeList->getTypes()));
