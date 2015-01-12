<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $this \Magento\Sales\Model\Resource\Setup */
$this->startSetup();
/**
 * update table 'sales_order_item'
 */
$table = $this->getConnection()->dropColumn('sales_order_item', 'is_nominal');
$this->endSetup();
