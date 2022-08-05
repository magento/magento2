<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$orderCollection = $objectManager->create(\Magento\Sales\Model\Order::class)->getCollection();
$order = $orderCollection->getFirstItem();

$creditmemoItemFactory = $objectManager->create(\Magento\Sales\Model\Order\Creditmemo\ItemFactory::class);
/** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Magento\Sales\Model\Order\Creditmemo::STATE_OPEN);
foreach ($order->getItems() as $item) {
    $creditmemoItem = $creditmemoItemFactory->create(
        ['data' => [
                'order_item_id' => $item->getId(),
                'sku' => $item->getSku(),
            ],
        ]
    );
    $creditmemo->addItem($creditmemoItem);
}
$creditmemo->save();
