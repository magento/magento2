<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceItemRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Invoice\ItemFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_bundle_and_invoiced.php');

$objectManager = ObjectManager::getInstance();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

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
