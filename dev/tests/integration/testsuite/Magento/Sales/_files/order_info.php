<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->setTypeId('virtual')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty'                     => 100,
        'is_qty_decimal'          => 0,
        'is_in_stock'             => 1,
    ])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();
$product->load(1);

$addressData = include __DIR__ . '/address_data.php';

$billingAddress = $objectManager->create('Magento\Quote\Model\Quote\Address', ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->setCustomerIsGuest(
    true
)->setStoreId(
    $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId()
)->setReservedOrderId(
    '100000001'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->addProduct(
    $product,
    10
);
$quote->getPayment()->setMethod('checkmo');
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->save();

$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getShippingAddress()->collectShippingRates();
$quote->collectTotals();
$quote->save();

$quote->setCustomerEmail('admin@example.com');
$quoteManagement = $objectManager->create('Magento\Quote\Model\QuoteManagement');

$order = $quoteManagement->submit($quote, ['increment_id' => '100000001']);

/** @var $item \Magento\Sales\Model\Order\Item */
$item = $order->getAllItems()[0];

/** @var \Magento\Sales\Model\Service\Order $orderService */
$orderService = $objectManager->create('Magento\Sales\Model\Service\Order', ['order' => $order]);

/** @var $invoice \Magento\Sales\Model\Order\Invoice */
$invoice = $orderService->prepareInvoice([$item->getId() => 10]);
$invoice->register();
$invoice->save();

$creditmemo = $orderService->prepareInvoiceCreditmemo($invoice, ['qtys' => [$item->getId() => 5]]);

foreach ($creditmemo->getAllItems() as $creditmemoItem) {
    //Workaround to return items to stock
    $creditmemoItem->setBackToStock(true);
}

$creditmemo->register();
$creditmemo->save();

/** @var \Magento\Framework\DB\Transaction $transactionSave */
$transactionSave = $objectManager->create('Magento\Framework\DB\Transaction')
    ->addObject($creditmemo)
    ->addObject($creditmemo->getOrder());
if ($creditmemo->getInvoice()) {
    $transactionSave->addObject($creditmemo->getInvoice());
}

$transactionSave->save();
