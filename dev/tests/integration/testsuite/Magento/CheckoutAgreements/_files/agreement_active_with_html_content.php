<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
$agreement = $objectManager->create('Magento\CheckoutAgreements\Model\Agreement');
$agreement->setData([
    'name' => 'Checkout Agreement (active)',
    'content' => 'Checkout agreement content: <b>HTML</b>',
    'content_height' => '200px',
    'checkbox_text' => 'Checkout agreement checkbox text.',
    'is_active' => true,
    'is_html' => true,
    'stores' => [0, 1],
]);
$agreement->save();
