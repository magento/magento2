<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'default_rollback.php';
require __DIR__ . '/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001');

/** @var Magento\Sales\Model\Service\Order  $service */
$service = $objectManager->get('Magento\Sales\Model\Service\Order');
$creditmemo = $service->prepareCreditmemo($order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Magento\Sales\Model\Order\Creditmemo::STATE_OPEN);
$creditmemo->setIncrementId('100000001');
$creditmemo->save();

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->get('Magento\Sales\Model\Order\Item');
$orderItem->setName('Test item')
    ->setQtyRefunded(1)
    ->setQtyInvoiced(10)
    ->setId(1)
    ->setOriginalPrice(20);

/** @var \Magento\Sales\Model\Order\Creditmemo\Item $creditItem */
$creditItem = $objectManager->get('Magento\Sales\Model\Order\Creditmemo\Item');
$creditItem->setCreditmemo($creditmemo)
    ->setOrderItem($orderItem)
    ->setName('Creditmemo item')
    ->setQty(1)
    ->setPrice(20)
    ->save();
