<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class DatInstaller
 * @package Magento\Directory\Setup
 */
class DatInstaller
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $data;

    /**
     * DatInstaller constructor.
     * @param \Magento\Directory\Helper\Data $data
     */
    public function __construct(
        \Magento\Directory\Helper\Data $data
    ) {
        $this->data = $data;
    }

    /**
     * Add country-region data.
     *
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryRegions(AdapterInterface $adapter,  array $data)
    {
        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $adapter->insert($adapter->getTableName('directory_country_region'), $bind);
            $regionId = $adapter->lastInsertId($adapter->getTableName('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($adapter->getTableName('directory_country_region_name'), $bind);
        }
        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $countries = $this->data->getCountryCollection()->getCountriesWithRequiredStates();
        $adapter->update(
            $adapter->getTableName('core_config_data'),
            [
                'value' => implode(',', array_keys($countries))
            ],
            [
                'scope="default"',
                'scope_id=0',
                'path=?' => \Magento\Directory\Helper\Data::XML_PATH_STATES_REQUIRED
            ]
        );
    }
}
