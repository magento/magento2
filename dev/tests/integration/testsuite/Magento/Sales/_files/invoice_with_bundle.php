<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\InvoiceItemRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Invoice\ItemFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\ObjectManager;

require 'order_with_bundle_and_invoiced.php';
/** @var \Magento\Sales\Model\Order $order */

$objectManager = ObjectManager::getInstance();

/** @var InvoiceService $invoiceService */
$invoiceService = $objectManager->get(InvoiceService::class);
$invoice = $invoiceService->prepareInvoice($order);
$invoice->setIncrementId('100000001');
$invoice->register();

/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
$invoice = $invoiceRepository->save($invoice);

/** @var ItemFactory $itemFactory */
$itemFactory = $objectManager->get(ItemFactory::class);
/** @var InvoiceItemRepositoryInterface $itemRepository */
$itemRepository = $objectManager->get(InvoiceItemRepositoryInterface::class);

foreach ($order->getAllItems() as $item) {
    $invoiceItem = $itemFactory->create(['data' => $item->getData()]);
    $invoiceItem->setId(null)
        ->setInvoice($invoice)
        ->setOrderItem($item)
        ->setQty($item->getQtyInvoiced());
    $itemRepository->save($invoiceItem);
}
