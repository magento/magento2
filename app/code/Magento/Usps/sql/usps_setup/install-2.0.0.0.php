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

$installer = $this;
$configDataTable = $installer->getTable('core_config_data');
$connection = $installer->getConnection();

$oldToNewMethodCodesMap = array(
    'First-Class' => '0_FCLE',
    'First-Class Mail International Large Envelope' => 'INT_14',
    'First-Class Mail International Letter' => 'INT_13',
    'First-Class Mail International Letters' => 'INT_13',
    'First-Class Mail International Package' => 'INT_15',
    'First-Class Mail International Parcel' => 'INT_13',
    'First-Class Package International Service' => 'INT_15',
    'First-Class Mail' => '0_FCLE',
    'First-Class Mail Flat' => '0_FCLE',
    'First-Class Mail Large Envelope' => '0_FCLE',
    'First-Class Mail International' => 'INT_14',
    'First-Class Mail Letter' => '0_FCL',
    'First-Class Mail Parcel' => '0_FCP',
    'First-Class Mail Package' => '0_FCP',
    'Parcel Post' => '4',
    'Standard Post' => '4',
    'Media Mail' => '6',
    'Library Mail' => '7',
    'Express Mail' => '3',
    'Express Mail PO to PO' => '3',
    'Express Mail Flat Rate Envelope' => '13',
    'Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee' => '25',
    'Express Mail Sunday/Holiday Guarantee' => '23',
    'Express Mail Flat Rate Envelope Hold For Pickup' => '27',
    'Express Mail Hold For Pickup' => '2',
    'Global Express Guaranteed (GXG)' => 'INT_4',
    'Global Express Guaranteed Non-Document Rectangular' => 'INT_6',
    'Global Express Guaranteed Non-Document Non-Rectangular' => 'INT_7',
    'USPS GXG Envelopes' => 'INT_12',
    'Express Mail International' => 'INT_1',
    'Express Mail International Flat Rate Envelope' => 'INT_10',
    'Priority Mail' => '1',
    'Priority Mail Small Flat Rate Box' => '28',
    'Priority Mail Medium Flat Rate Box' => '17',
    'Priority Mail Large Flat Rate Box' => '22',
    'Priority Mail Flat Rate Envelope' => '16',
    'Priority Mail International' => 'INT_2',
    'Priority Mail International Flat Rate Envelope' => 'INT_8',
    'Priority Mail International Small Flat Rate Box' => 'INT_16',
    'Priority Mail International Medium Flat Rate Box' => 'INT_9',
    'Priority Mail International Large Flat Rate Box' => 'INT_11'
);

$select = $connection->select()->from(
    $configDataTable
)->where(
    'path IN (?)',
    array('carriers/usps/free_method', 'carriers/usps/allowed_methods')
);
$oldConfigValues = $connection->fetchAll($select);

foreach ($oldConfigValues as $oldValue) {
    $newValue = '';
    if (stripos($oldValue['path'], 'free_method') && isset($oldToNewMethodCodesMap[$oldValue['value']])) {
        $newValue = $oldToNewMethodCodesMap[$oldValue['value']];
    } elseif (stripos($oldValue['path'], 'allowed_methods')) {
        $newValuesList = array();
        foreach (explode(',', $oldValue['value']) as $shippingMethod) {
            if (isset($oldToNewMethodCodesMap[$shippingMethod])) {
                $newValuesList[] = $oldToNewMethodCodesMap[$shippingMethod];
            }
        }
        $newValue = implode(',', $newValuesList);
    } else {
        continue;
    }

    if ($newValue && $newValue != $oldValue['value']) {
        $whereConfigId = $connection->quoteInto('config_id = ?', $oldValue['config_id']);
        $connection->update($configDataTable, array('value' => $newValue), $whereConfigId);
    }
}
