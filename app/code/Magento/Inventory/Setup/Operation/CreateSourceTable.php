<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;

class CreateSourceTable
{
    /**
     * Constant for decimal precision for latitude and longitude
     */
    const LATLON_PRECISION_LAT = 8;
    const LATLON_PRECISION_LON = 9;
    const LATLON_SCALE = 6;

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $sourceTable = $setup->getConnection()->newTable(
            $setup->getTable(SourceResourceModel::TABLE_NAME_SOURCE)
        )->setComment(
            'Inventory Source Table'
        );

        $sourceTable = $this->addBaseFields($sourceTable);
        $sourceTable = $this->addAddressFields($sourceTable);
        $sourceTable = $this->addContactInfoFields($sourceTable);
        $sourceTable = $this->addSourceCarrierFields($sourceTable);

        $setup->getConnection()->createTable($sourceTable);
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addBaseFields(Table $sourceTable): Table
    {
        return $sourceTable->addColumn(
            SourceInterface::SOURCE_CODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Source Code'
        )->addColumn(
            SourceInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Source Name'
        )->addColumn(
            SourceInterface::ENABLED,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_DEFAULT => 1,
            ],
            'Defines Is Source Enabled'
        )->addColumn(
            SourceInterface::DESCRIPTION,
            Table::TYPE_TEXT,
            1000,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Description'
        )->addColumn(
            SourceInterface::LATITUDE,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_PRECISION => self::LATLON_PRECISION_LAT,
                Table::OPTION_SCALE => self::LATLON_SCALE,
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => true,
            ],
            'Latitude'
        )->addColumn(
            SourceInterface::LONGITUDE,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_PRECISION => self::LATLON_PRECISION_LON,
                Table::OPTION_SCALE => self::LATLON_SCALE,
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => true,
            ],
            'Longitude'
        )->addColumn(
            SourceInterface::PRIORITY,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Priority'
        );
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addAddressFields(Table $sourceTable): Table
    {
        $sourceTable->addColumn(
            SourceInterface::COUNTRY_ID,
            Table::TYPE_TEXT,
            2,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Country Id'
        )->addColumn(
            SourceInterface::REGION_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Region Id'
        )->addColumn(
            SourceInterface::REGION,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Region'
        )->addColumn(
            SourceInterface::CITY,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'City'
        )->addColumn(
            SourceInterface::STREET,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Street'
        )->addColumn(
            SourceInterface::POSTCODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Postcode'
        );
        return $sourceTable;
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addContactInfoFields(Table $sourceTable): Table
    {
        $sourceTable->addColumn(
            SourceInterface::CONTACT_NAME,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Contact Name'
        )->addColumn(
            SourceInterface::EMAIL,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Email'
        )->addColumn(
            SourceInterface::PHONE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Phone'
        )->addColumn(
            SourceInterface::FAX,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => true,
            ],
            'Fax'
        );
        return $sourceTable;
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addSourceCarrierFields(Table $sourceTable): Table
    {
        $sourceTable->addColumn(
            'use_default_carrier_config',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => '1'
            ],
            'Use default carrier configuration'
        );
        return $sourceTable;
    }
}
