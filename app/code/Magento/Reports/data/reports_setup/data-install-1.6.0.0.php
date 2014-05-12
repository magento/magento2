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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Reports\Model\Resource\Setup */
$installer = $this;
/*
 * Prepare database for data upgrade
 */
$installer->startSetup();
/*
 * Report Event Types default data
 */
$eventTypeData = array(
    array('event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW, 'event_name' => 'catalog_product_view'),
    array('event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_SEND, 'event_name' => 'sendfriend_product'),
    array(
        'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_COMPARE,
        'event_name' => 'catalog_product_compare_add_product'
    ),
    array(
        'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART,
        'event_name' => 'checkout_cart_add_product'
    ),
    array(
        'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_WISHLIST,
        'event_name' => 'wishlist_add_product'
    ),
    array('event_type_id' => \Magento\Reports\Model\Event::EVENT_WISHLIST_SHARE, 'event_name' => 'wishlist_share')
);

foreach ($eventTypeData as $row) {
    $installer->getConnection()->insertForce($installer->getTable('report_event_types'), $row);
}

/**
 * Prepare database after data upgrade
 */
$installer->endSetup();

/**
 * Cms Page  with 'home' identifier page modification for report pages
 */
/** @var $cms \Magento\Cms\Model\Page */
$cms = $installer->getPage()->load('home', 'identifier');

$reportLayoutUpdate = '<!--
    <referenceContainer name="right">
        <action method="unsetChild"><argument name="alias" xsi:type="string">right.reports.product.viewed</argument></action>
        <action method="unsetChild"><argument name="alias" xsi:type="string">right.reports.product.compared</argument></action>
    </referenceContainer>-->';

/*
 * Merge and save old layout update data with report layout data
 */
$cms->setLayoutUpdateXml($cms->getLayoutUpdateXml() . $reportLayoutUpdate)->save();
