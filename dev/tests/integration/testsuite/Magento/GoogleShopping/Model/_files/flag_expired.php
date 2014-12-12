<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Expired flag for the google shopping synchronization
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $flag \Magento\GoogleShopping\Model\Flag */
$flag = $objectManager->create('Magento\GoogleShopping\Model\Flag');
$flag->lock();

/** @var $flagResource \Magento\Framework\Flag\Resource */
$flagResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Framework\Flag\Resource');
$flag->setLastUpdate(date('Y-m-d H:i:s', time() - \Magento\GoogleShopping\Model\Flag::FLAG_TTL - 1));
$flag->setKeepUpdateDate(true);
$flagResource->save($flag);
