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

/** @var \Magento\Framework\Module\Setup $this */
$codes = array(
    'method' => array(
        'EUROPEFIRSTINTERNATIONALPRIORITY' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
        'FEDEX1DAYFREIGHT' => 'FEDEX_1_DAY_FREIGHT',
        'FEDEX2DAYFREIGHT' => 'FEDEX_2_DAY_FREIGHT',
        'FEDEX2DAY' => 'FEDEX_2_DAY',
        'FEDEX3DAYFREIGHT' => 'FEDEX_3_DAY_FREIGHT',
        'FEDEXEXPRESSSAVER' => 'FEDEX_EXPRESS_SAVER',
        'FEDEXGROUND' => 'FEDEX_GROUND',
        'FIRSTOVERNIGHT' => 'FIRST_OVERNIGHT',
        'GROUNDHOMEDELIVERY' => 'GROUND_HOME_DELIVERY',
        'INTERNATIONALECONOMY' => 'INTERNATIONAL_ECONOMY',
        'INTERNATIONALECONOMY FREIGHT' => 'INTERNATIONAL_ECONOMY_FREIGHT',
        'INTERNATIONALFIRST' => 'INTERNATIONAL_FIRST',
        'INTERNATIONALGROUND' => 'INTERNATIONAL_GROUND',
        'INTERNATIONALPRIORITY' => 'INTERNATIONAL_PRIORITY',
        'INTERNATIONALPRIORITY FREIGHT' => 'INTERNATIONAL_PRIORITY_FREIGHT',
        'PRIORITYOVERNIGHT' => 'PRIORITY_OVERNIGHT',
        'SMARTPOST' => 'SMART_POST',
        'STANDARDOVERNIGHT' => 'STANDARD_OVERNIGHT',
        'FEDEXFREIGHT' => 'FEDEX_FREIGHT',
        'FEDEXNATIONALFREIGHT' => 'FEDEX_NATIONAL_FREIGHT'
    ),
    'dropoff' => array(
        'REGULARPICKUP' => 'REGULAR_PICKUP',
        'REQUESTCOURIER' => 'REQUEST_COURIER',
        'DROPBOX' => 'DROP_BOX',
        'BUSINESSSERVICECENTER' => 'BUSINESS_SERVICE_CENTER',
        'STATION' => 'STATION'
    ),
    'packaging' => array(
        'FEDEXENVELOPE' => 'FEDEX_ENVELOPE',
        'FEDEXPAK' => 'FEDEX_PAK',
        'FEDEXBOX' => 'FEDEX_BOX',
        'FEDEXTUBE' => 'FEDEX_TUBE',
        'FEDEX10KGBOX' => 'FEDEX_10KG_BOX',
        'FEDEX25KGBOX' => 'FEDEX_25KG_BOX',
        'YOURPACKAGING' => 'YOUR_PACKAGING'
    )
);

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;
$configDataTable = $installer->getTable('core_config_data');
$conn = $installer->getConnection();

$select = $conn->select()->from(
    $configDataTable
)->where(
    'path IN (?)',
    array(
        'carriers/fedex/packaging',
        'carriers/fedex/dropoff',
        'carriers/fedex/free_method',
        'carriers/fedex/allowed_methods'
    )
);
$mapsOld = $conn->fetchAll($select);
foreach ($mapsOld as $mapOld) {
    $mapNew = '';
    if (stripos($mapOld['path'], 'packaging') && isset($codes['packaging'][$mapOld['value']])) {
        $mapNew = $codes['packaging'][$mapOld['value']];
    } else if (stripos($mapOld['path'], 'dropoff') && isset($codes['dropoff'][$mapOld['value']])) {
        $mapNew = $codes['dropoff'][$mapOld['value']];
    } else if (stripos($mapOld['path'], 'free_method') && isset($codes['method'][$mapOld['value']])) {
        $mapNew = $codes['method'][$mapOld['value']];
    } else if (stripos($mapOld['path'], 'allowed_methods')) {
        foreach (explode(',', $mapOld['value']) as $shippingMethod) {
            if (isset($codes['method'][$shippingMethod])) {
                $mapNew[] = $codes['method'][$shippingMethod];
            } else {
                $mapNew[] = $shippingMethod;
            }
        }
        $mapNew = implode($mapNew, ',');
    } else {
        continue;
    }

    if (!empty($mapNew) && $mapNew != $mapOld['value']) {
        $whereConfigId = $conn->quoteInto('config_id = ?', $mapOld['config_id']);
        $conn->update($configDataTable, array('value' => $mapNew), $whereConfigId);
    }
}
