<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/** @var $cache \Magento\Framework\App\Cache */
$cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\App\Cache::class);
$cache->clean();
