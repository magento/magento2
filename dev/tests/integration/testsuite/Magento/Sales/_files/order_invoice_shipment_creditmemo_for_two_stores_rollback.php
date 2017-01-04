<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require 'default_rollback.php';
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection $invoiceGridCollection */
$invoiceGridCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection::class);
$invoiceGridCollection->getConnection()->truncateTable($invoiceGridCollection->getMainTable());

/** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection $shipmentCollection */
$shipmentCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection::class);
$shipmentCollection->getConnection()->truncateTable($shipmentCollection->getMainTable());

/** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection $creditmemoCollection */
$creditmemoCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection::class);
$creditmemoCollection->getConnection()->truncateTable($creditmemoCollection->getMainTable());

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);