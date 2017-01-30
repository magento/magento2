<?php
/**
 * Invoice, shipment, creditmemo for two stores fixture
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$filesArray = ['order.php','order_fixture_second_store.php'];

foreach ($filesArray as $file) {
    require $file;
    /** @var \Magento\Sales\Model\Order $order */
    /** @var \Magento\Sales\Model\Order\Item $orderItem */

    /** @var \Magento\Sales\Api\InvoiceManagementInterface $orderService */
    $orderService = $objectManager->create(
        \Magento\Sales\Api\InvoiceManagementInterface::class
    );
    $orderIncrementId = $order->getIncrementId();
    /** @var \Magento\Sales\Model\Order\Invoice $invoice */
    $invoice = $orderService->prepareInvoice($order);
    $invoice->register();
    $order->setIsInProcess(true);
    /** @var \Magento\Framework\DB\Transaction $transactionSave */
    $transactionSave = $objectManager
        ->create(\Magento\Framework\DB\Transaction::class);
    $transactionSave->addObject($invoice)->addObject($order)->save();

    /** @var Magento\Sales\Model\Order\Payment $payment */
    $payment = $order->getPayment();
    $paymentInfoBlock = $objectManager
        ->get(\Magento\Payment\Helper\Data::class)
        ->getInfoBlock($payment);
    $payment->setBlockMock($paymentInfoBlock);

    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
    $shipment = $objectManager->create(\Magento\Sales\Model\Order\Shipment::class);
    $shipment->setOrder($order);

    /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
    $shipmentItem = $objectManager->create(\Magento\Sales\Model\Order\Shipment\Item::class);
    $shipmentItem->setOrderItem($orderItem);
    $shipment->addItem($shipmentItem);
    $shipment->setPackages([['1'], ['2']]);
    $shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);

    $shipment->save();

    /** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
    $creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
    /** @var Magento\Sales\Model\Order\Creditmemo $creditmemo */
    $creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
    $creditmemo->setOrder($order);
    $creditmemo->setState(Magento\Sales\Model\Order\Creditmemo::STATE_OPEN);
    $creditmemo->setIncrementId($orderIncrementId);
    $creditmemo->save();
}
