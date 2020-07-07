<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Sales/_files/customer_invoice_with_two_products_and_custom_options.php'
);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditMemoFactory */
$creditMemoFactory = $objectManager->create(\Magento\Sales\Model\Order\CreditmemoFactory::class);
/** @var \Magento\Sales\Model\Service\CreditmemoService $creditMemoService */
$creditMemoService = $objectManager->create(\Magento\Sales\Model\Service\CreditmemoService::class);
/** @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);

$creditMemo = $creditMemoFactory->createByOrder($orderRepository->get(2));
$creditMemo->setAdjustment(1.23);

$creditMemoService->refund($creditMemo);
