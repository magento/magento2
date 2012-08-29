<?php
/**
 * Script to retrieve number of orders after checkout scenario execution, and to compare it to the expected value.
 *
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
 * @package     Mage_Sales
 * @subpackage  performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// Parse parameters
$params = getopt('', array('beforeOutput:', 'scenarioExecutions:'));
preg_match("/Num orders: (\\d+)/", $params['beforeOutput'], $matches);
$numOrdersBefore = $matches[1];
$expectedOrdersCreated = $params['scenarioExecutions'];

// Retrieve current number of orders and calculate number of orders created
require_once __DIR__ . '/../../../../app/bootstrap.php';
Mage::app('', 'store');
$collection = new Mage_Sales_Model_Resource_Order_Collection();
$numOrdersNow = $collection->getSize();
$actualOrdersCreated = $numOrdersNow - $numOrdersBefore;

// Compare number of new orders to the expected value
if ($expectedOrdersCreated != $actualOrdersCreated) {
    echo "Failure: expected {$expectedOrdersCreated} new orders, while actually created {$actualOrdersCreated}";
    exit(1);
}

echo "Verification successful, {$actualOrdersCreated} of {$expectedOrdersCreated} orders created";
