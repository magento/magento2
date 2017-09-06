<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order_express.php';

$subTotal = 121;
$taxRate = .0825;
$taxAmount = $subTotal * $taxRate;
$shippingAmount = 15;
$totalAmount =  $subTotal + $taxAmount + $shippingAmount;

$order->setSubtotal($subTotal);
$order->setBaseSubtotal($subTotal);
$order->setGrandTotal($totalAmount);
$order->setBaseGrandTotal($totalAmount);
$order->setTaxAmount($taxAmount);

$order->save();
