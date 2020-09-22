<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture(
    'Magento/Sales/_files/customer_invoice_with_two_products_and_custom_options.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var CreditmemoFactory $creditMemoFactory */
$creditMemoFactory = $objectManager->create(CreditmemoFactory::class);
/** @var CreditmemoService $creditMemoService */
$creditMemoService = $objectManager->create(CreditmemoService::class);

/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

$creditMemo = $creditMemoFactory->createByOrder($order);
$creditMemo->setAdjustment(1.23);
$creditMemo->setBaseGrandTotal(10);
$creditMemo->addComment('some_comment', false, true);
$creditMemo->addComment('some_other_comment', false, true);
$creditMemo->addComment('not_visible', false, false);

$creditMemoService->refund($creditMemo);
