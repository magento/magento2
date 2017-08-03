<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Fedex\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $codes = [
            'method' => [
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
                'FEDEXNATIONALFREIGHT' => 'FEDEX_NATIONAL_FREIGHT',
            ],
            'dropoff' => [
                'REGULARPICKUP' => 'REGULAR_PICKUP',
                'REQUESTCOURIER' => 'REQUEST_COURIER',
                'DROPBOX' => 'DROP_BOX',
                'BUSINESSSERVICECENTER' => 'BUSINESS_SERVICE_CENTER',
                'STATION' => 'STATION',
            ],
            'packaging' => [
                'FEDEXENVELOPE' => 'FEDEX_ENVELOPE',
                'FEDEXPAK' => 'FEDEX_PAK',
                'FEDEXBOX' => 'FEDEX_BOX',
                'FEDEXTUBE' => 'FEDEX_TUBE',
                'FEDEX10KGBOX' => 'FEDEX_10KG_BOX',
                'FEDEX25KGBOX' => 'FEDEX_25KG_BOX',
                'YOURPACKAGING' => 'YOUR_PACKAGING',
            ],
        ];

        $installer = $setup;
        $configDataTable = $installer->getTable('core_config_data');
        $conn = $installer->getConnection();

        $select = $conn->select()->from(
            $configDataTable
        )->where(
            'path IN (?)',
            [
                'carriers/fedex/packaging',
                'carriers/fedex/dropoff',
                'carriers/fedex/free_method',
                'carriers/fedex/allowed_methods'
            ]
        );
        $mapsOld = $conn->fetchAll($select);
        foreach ($mapsOld as $mapOld) {
            $mapNew = '';
            if (stripos($mapOld['path'], 'packaging') && isset($codes['packaging'][$mapOld['value']])) {
                $mapNew = $codes['packaging'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'dropoff') && isset($codes['dropoff'][$mapOld['value']])) {
                $mapNew = $codes['dropoff'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'free_method') && isset($codes['method'][$mapOld['value']])) {
                $mapNew = $codes['method'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'allowed_methods')) {
                foreach (explode(',', $mapOld['value']) as $shippingMethod) {
                    if (isset($codes['method'][$shippingMethod])) {
                        $mapNew[] = $codes['method'][$shippingMethod];
                    } else {
                        $mapNew[] = $shippingMethod;
                    }
                }
                $mapNew = implode(',', $mapNew);
            } else {
                continue;
            }

            if (!empty($mapNew) && $mapNew != $mapOld['value']) {
                $whereConfigId = $conn->quoteInto('config_id = ?', $mapOld['config_id']);
                $conn->update($configDataTable, ['value' => $mapNew], $whereConfigId);
            }
        }
    }
}
