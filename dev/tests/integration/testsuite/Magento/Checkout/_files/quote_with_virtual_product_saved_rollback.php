<?php
/**
 * Rollback for quote_with_payment_saved.php fixture.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->load('test_order_with_virtual_product_without_address', 'reserved_order_id')->delete();
