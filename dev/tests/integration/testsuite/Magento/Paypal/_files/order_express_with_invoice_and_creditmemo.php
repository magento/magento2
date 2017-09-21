<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;

require __DIR__ . '/order_express_with_invoice_and_shipping.php';

/** @var CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->create(CreditmemoFactory::class);
/** @var Creditmemo $creditmemo */
$creditmemo = $creditmemoFactory->createByInvoice($invoice, $invoice->getData());

$creditmemo->setOrder($order);
$creditmemo->setState(Creditmemo::STATE_REFUNDED);
$creditmemo->setIncrementId('100000001');
$creditmemo->setGrandTotal($itemsAmount);

/** @var CreditmemoRepositoryInterface $creditMemoRepository */
$creditMemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
$creditMemoRepository->save($creditmemo);
