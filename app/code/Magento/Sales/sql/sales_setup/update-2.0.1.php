<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Sales\Model\Resource\Setup */
$this->startSetup();
/**
 * update table 'sales_order_item'
 */
$table = $this->getConnection()->dropColumn('sales_order_item', 'is_nominal');
$this->endSetup();
