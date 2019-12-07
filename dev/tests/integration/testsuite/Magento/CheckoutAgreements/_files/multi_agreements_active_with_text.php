<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
$agreement = $objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
$agreement->setData([
    'name' => 'First Checkout Agreement (active)',
    'content' => 'Checkout agreement content: TEXT',
    'content_height' => '200px',
    'checkbox_text' => 'Checkout agreement checkbox text.',
    'is_active' => true,
    'is_html' => false,
    'mode' => 1,
    'stores' => [0, 1],
]);
$agreement->save();
$agreement = $objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
$agreement->setData([
    'name' => 'Second Checkout Agreement (active)',
    'content' => 'Checkout agreement content: TEXT',
    'content_height' => '200px',
    'checkbox_text' => 'Checkout agreement checkbox text.',
    'is_active' => true,
    'is_html' => false,
    'mode' => 1,
    'stores' => [0, 1],
]);
$agreement->save();
