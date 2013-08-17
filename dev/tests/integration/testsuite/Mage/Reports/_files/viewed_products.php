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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../../Mage/Catalog/_files/product_simple.php';
require __DIR__ . '/../../../Mage/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Mage/Catalog/_files/product_virtual.php';

// imitate product views
/** @var Mage_Reports_Model_Event_Observer $reportObserver */
$reportObserver = Mage::getModel('Mage_Reports_Model_Event_Observer');
foreach (array(1, 2, 1, 21, 1, 21) as $productId) {
    $reportObserver->catalogProductView(new Varien_Event_Observer(array(
        'event' => new Varien_Object(array(
            'product' => new Varien_Object(array('id' => $productId))
        ))
    )));
}

// refresh report statistics
/** @var Mage_Reports_Model_Resource_Report_Product_Viewed $reportResource */
$reportResource = Mage::getResourceModel('Mage_Reports_Model_Resource_Report_Product_Viewed');
$reportResource->beginTransaction(); // prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
