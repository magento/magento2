<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Store\Model\StoreManagerInterface'
)->getDefaultStoreView()->getId();
/** @var $change \Magento\Core\Model\Design */
$change = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Design');
$change->setStoreId($storeId)->setDesign('Magento/luma')->setDateFrom('2001-01-01 01:01:01')->save();
