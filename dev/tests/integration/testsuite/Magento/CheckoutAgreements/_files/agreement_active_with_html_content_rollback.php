<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
$agreement = $objectManager->create('Magento\CheckoutAgreements\Model\Agreement');
$agreement->load('Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreement->delete();
}
