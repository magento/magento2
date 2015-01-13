<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\ToolkitFramework\Application $this */
$this->resetObjectManager();
/**
 * @var \Magento\Framework\App\Config\Value $configData
 */
$configData = $this->getObjectManager()->create('Magento\Framework\App\Config\Value');
$configData->setPath('carriers/flatrate/active')
    ->setScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT)
    ->setScopeId(0)
    ->setValue(1)
    ->save();

$this->getObjectManager()->get('Magento\Framework\App\CacheInterface')
    ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
