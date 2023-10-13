<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Add Required Regions for Country
 */
class DataInstaller
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * DatInstaller constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Add country-region data.
     *
     * @param  AdapterInterface $adapter
     * @param  array $data
     * @return void
     */
    public function addCountryRegions(AdapterInterface $adapter, array $data): void
    {
        $where = [
            $adapter->quoteInto('path = ?', Data::XML_PATH_STATES_REQUIRED),
            $adapter->quoteInto('scope = ?', 'default'),
            $adapter->quoteInto('scope_id = ?', 0),
        ];

        $select = $adapter->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), 'value')
            ->where(implode(' AND ', $where));

        $currRequiredStates = $adapter->fetchOne($select);
        $currRequiredStates = (!empty($currRequiredStates)) ? explode(',', $currRequiredStates) : [];

        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region'), $bind);
            $regionId = $adapter->lastInsertId($this->resourceConnection->getTableName('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region_name'), $bind);

            if (!in_array($row[0], $currRequiredStates)) {
                $currRequiredStates[] = $row[0];
            }
        }

        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $adapter->update(
            $this->resourceConnection->getTableName('core_config_data'),
            [
                'value' => implode(',', $currRequiredStates)
            ],
            $where
        );
    }
}
