<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var \Magento\TestFramework\Application $this */

/**
 * @var \Magento\Framework\App\Config\ValueInterface $configData
 */
$configData = $this->getObjectManager()->create('Magento\Framework\App\Config\ValueInterface');
$configData->setPath(
    'catalog/frontend/flat_catalog_product'
)->setScope(
    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
)->setScopeId(
    0
)->setValue(
    1
)->save();

$this->getObjectManager()->get('Magento\Framework\App\CacheInterface')
    ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
