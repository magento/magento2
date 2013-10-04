<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();
$itemsPerPage = 1000;
$currentPosition = 0;

/** Update sales order payment */
do {
    $select = $installer->getConnection()
        ->select()
        ->from(
        $installer->getTable('sales_flat_order_payment'),
        array('entity_id', 'cc_owner', 'cc_exp_month', 'cc_exp_year', 'method')
    )
        ->where('method = ?', 'ccsave')
        ->limit($itemsPerPage, $currentPosition);

    $orders = $select->query()->fetchAll();
    $currentPosition += $itemsPerPage;

    foreach ($orders as $order) {
        $installer->getConnection()
            ->update(
                $installer->getTable('sales_flat_order_payment'),
                array(
                    'cc_exp_month' => $installer->getCoreData()->encrypt($order['cc_exp_month']),
                    'cc_exp_year' => $installer->getCoreData()->encrypt($order['cc_exp_year']),
                    'cc_owner' => $installer->getCoreData()->encrypt($order['cc_owner']),
                ),
                array('entity_id = ?' => $order['entity_id'])
        );
    }

} while (count($orders) > 0);

/** Update sales quote payment */
$currentPosition = 0;
do {
    $select = $installer->getConnection()
        ->select()
        ->from(
        $installer->getTable('sales_flat_quote_payment'),
        array('payment_id', 'cc_owner', 'cc_exp_month', 'cc_exp_year', 'method')
    )
        ->where('method = ?', 'ccsave')
        ->limit($itemsPerPage, $currentPosition);

    $quotes = $select->query()->fetchAll();
    $currentPosition += $itemsPerPage;

    foreach ($quotes as $quote) {
        $installer->getConnection()
            ->update(
                $installer->getTable('sales_flat_quote_payment'),
                array(
                    'cc_exp_month' => $installer->getCoreData()->encrypt($quote['cc_exp_month']),
                    'cc_exp_year' => $installer->getCoreData()->encrypt($quote['cc_exp_year']),
                    'cc_owner' => $installer->getCoreData()->encrypt($quote['cc_owner']),
                ),
                array('payment_id = ?' => $quote['payment_id'])
        );
    }

} while (count($quotes) > 0);

$installer->endSetup();
