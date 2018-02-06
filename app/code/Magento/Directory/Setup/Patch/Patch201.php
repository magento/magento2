<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup\Patch;

use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param Data $directoryData
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
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $this->addCountryRegions($setup, $this->getDataForCroatia());

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function addCountryRegions(ModuleDataSetupInterface $setup, array $data
    )
    {
        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $setup->getConnection()->insert($setup->getTable('directory_country_region'), $bind);
            $regionId = $setup->getConnection()->lastInsertId($setup->getTable('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $setup->getConnection()->insert($setup->getTable('directory_country_region_name'), $bind);
        }
        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $countries = $this->directoryData->getCountryCollection()->getCountriesWithRequiredStates();
        $setup->getConnection()->update(
            $setup->getTable('core_config_data'),
            [
                'value' => implode(',', array_keys($countries))
            ],
            [
                'scope="default"',
                'scope_id=0',
                'path=?' => Data::XML_PATH_STATES_REQUIRED
            ]
        );

    }
}
