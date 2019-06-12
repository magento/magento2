<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Fedex\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class ConfigureFedexDefaults implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * ConfigureFedexDefaults constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function apply()
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

        $conn = $this->moduleDataSetup->getConnection();
        $configDataTable = $this->moduleDataSetup->getTable('core_config_data');
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
            if (stripos($mapOld['path'], 'packaging') !== false && isset($codes['packaging'][$mapOld['value']])) {
                $mapNew = $codes['packaging'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'dropoff') !== false && isset($codes['dropoff'][$mapOld['value']])) {
                $mapNew = $codes['dropoff'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'free_method') !== false && isset($codes['method'][$mapOld['value']])) {
                $mapNew = $codes['method'][$mapOld['value']];
            } elseif (stripos($mapOld['path'], 'allowed_methods') !== false) {
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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
