<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Directory\Helper\Data;

/**
 * Upgrade Data script for Directory module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Directory data.
     *
     * @var Data
     */
    private $directoryData;

    /**
     * @param Data $directoryData
     */
    public function __construct(Data $directoryData)
    {
        $this->directoryData = $directoryData;
    }

    /**
     * Upgrades data for Directory module.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addCountryRegions($setup, 'HR', $this->getDataForCroatia());
        }
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->addCountryRegions($setup, 'IN', $this->getDataForIndia());
        }
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->addCountryRegions($setup, 'AU', $this->getDataForAustralia());
        }
    }

    /**
     * Croatian states data.
     *
     * @return array
     */
    private function getDataForCroatia()
    {
        return [
            'HR-01' => 'Zagrebačka županija',
            'HR-02' => 'Krapinsko-zagorska županija',
            'HR-03' => 'Sisačko-moslavačka županija',
            'HR-04' => 'Karlovačka županija',
            'HR-05' => 'Varaždinska županija',
            'HR-06' => 'Koprivničko-križevačka županija',
            'HR-07' => 'Bjelovarsko-bilogorska županija',
            'HR-08' => 'Primorsko-goranska županija',
            'HR-09' => 'Ličko-senjska županija',
            'HR-10' => 'Virovitičko-podravska županija',
            'HR-11' => 'Požeško-slavonska županija',
            'HR-12' => 'Brodsko-posavska županija',
            'HR-13' => 'Zadarska županija',
            'HR-14' => 'Osječko-baranjska županija',
            'HR-15' => 'Šibensko-kninska županija',
            'HR-16' => 'Vukovarsko-srijemska županija',
            'HR-17' => 'Splitsko-dalmatinska županija',
            'HR-18' => 'Istarska županija',
            'HR-19' => 'Dubrovačko-neretvanska županija',
            'HR-20' => 'Međimurska županija',
            'HR-21' => 'Grad Zagreb',
        ];
    }

    /**
     * Indian states data.
     *
     * @return array
     */
    private function getDataForIndia()
    {
        return [
            'AN' => 'Andaman and Nicobar Islands',
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CH' => 'Chandigarh',
            'CT' => 'Chhattisgarh',
            'DN' => 'Dadra and Nagar Haveli',
            'DD' => 'Daman and Diu',
            'DL' => 'Delhi',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JK' => 'Jammu and Kashmir',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'LD' => 'Lakshadweep',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OR' => 'Odisha',
            'PY' => 'Puducherry',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TG' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UT' => 'Uttarakhand',
            'WB' => 'West Bengal',
        ];
    }

    /**
     * Australian states data.
     *
     * @return array
     */
    private function getDataForAustralia()
    {
        return [
            'ACT' => 'Australian Capital Territory',
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'QLD' => 'Queensland',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'WA' => 'Western Australia',
            'NT' => 'Northern Territory'
        ];
    }

    /**
     * Add country regions data to appropriate tables.
     *
     * @param ModuleDataSetupInterface $setup
     * @param string $countryId
     * @param array $data
     * @return void
     */
    private function addCountryRegions(ModuleDataSetupInterface $setup, string $countryId, array $data)
    {
        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        foreach ($data as $code => $name) {
            $bind = ['country_id' => $countryId, 'code' => $code, 'default_name' => $name];
            $setup->getConnection()->insert($setup->getTable('directory_country_region'), $bind);
            $regionId = $setup->getConnection()->lastInsertId($setup->getTable('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $name];
            $setup->getConnection()->insert($setup->getTable('directory_country_region_name'), $bind);
        }

        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $setup->getConnection()->update(
            $setup->getTable('core_config_data'),
            [
                'value' => new \Zend_Db_Expr("CONCAT(value, '," . $countryId . "')")
            ],
            [
                'scope="default"',
                'scope_id=0',
                'path=?' => Data::XML_PATH_STATES_REQUIRED
            ]
        );
    }
}
