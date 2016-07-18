<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
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

$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerEmail('admin@example.com');
$quote->setCustomerIsGuest(true);
$quote->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$quote->setReservedOrderId('100000001');
$quote->setBillingAddress($billingAddress);
$quote->setShippingAddress($shippingAddress);
$quote->getPayment()->setMethod('checkmo');
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getShippingAddress()->collectShippingRates();

/** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);

/** @var \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory */
$cartItemFactory = $objectManager->get(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class);

/** @var \Magento\Quote\Api\Data\CartItemInterface $cartItem */
$cartItem = $cartItemFactory->create();
$cartItem->setQty(10);
$cartItem->setQuoteId($quote->getId());
$cartItem->setSku($product->getSku());
$cartItem->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

/** @var \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository */
$cartItemRepository = $objectManager->get(\Magento\Quote\Api\CartItemRepositoryInterface::class);
$cartItemRepository->save($cartItem);

/** @var \Magento\Quote\Api\CartManagementInterface $quoteManagement */
$quoteManagement = $objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);

$quote = $quoteRepository->get($quote->getId());
$order = $quoteManagement->submit($quote, ['increment_id' => '100000001']);

/** @var $item \Magento\Sales\Model\Order\Item */
$item = $order->getAllItems()[0];

/** @var \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory */
$invoiceFactory = $objectManager->get(\Magento\Sales\Api\InvoiceManagementInterface::class);

/** @var $invoice \Magento\Sales\Model\Order\Invoice */
$invoice = $invoiceFactory->prepareInvoice($order, [$item->getId() => 10]);
$invoice->register();
$invoice->save();

/** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByInvoice($invoice, ['qtys' => [$item->getId() => 5]]);

foreach ($creditmemo->getAllItems() as $creditmemoItem) {
    //Workaround to return items to stock
    $creditmemoItem->setBackToStock(true);
}

$creditmemoManagement = $objectManager->create(\Magento\Sales\Api\CreditmemoManagementInterface::class);
$creditmemoManagement->refund($creditmemo);
