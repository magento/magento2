<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Constant for table names of the model \Magento\Inventory\Model\Source
     */
    const TABLE_NAME_SOURCE = 'inventory_source';

    /**
     * Constant for table name of \Magento\Inventory\Model\SourceCarrierLink
     */
    const TABLE_NAME_SOURCE_CARRIER_LINK = 'inventory_source_carrier_link';

    /**
     * Constant for decimal precision for latitude and longitude
     */
    const LATLON_PRECISION_LAT = 8;
    const LATLON_PRECISION_LON = 9;
    const LATLON_SCALE = 6;

    /**
     * Option keys for column options
     */
    const OPTION_IDENTITY = 'identity';
    const OPTION_UNSIGNED = 'unsigned';
    const OPTION_NULLABLE = 'nullable';
    const OPTION_PRIMARY = 'primary';
    const OPTION_DEFAULT = 'default';
    const OPTION_TYPE = 'type';
    const OPTION_LENGTH = 'length';
    const OPTION_SCALE = 'scale';
    const OPTION_PRECISION = 'precision';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $sourceTable = $this->createSourceTable($setup);
        $sourceTable = $this->addAddressFields($sourceTable);
        $sourceTable = $this->addContactInfoFields($sourceTable);
        $sourceTable = $this->addSourceCarrierFields($sourceTable);
        $setup->getConnection()->createTable($sourceTable);

        $setup->getConnection()->createTable($this->createSourceCarrierLinkTable($setup));
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createSourceTable(SchemaSetupInterface $setup)
    {
        $sourceTable = $setup->getTable(InstallSchema::TABLE_NAME_SOURCE);

        return $setup->getConnection()->newTable(
            $sourceTable
        )->setComment(
            'Inventory Source Table'
        )->addColumn(
            SourceInterface::SOURCE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                InstallSchema::OPTION_IDENTITY => true,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_PRIMARY => true,
            ],
            'Source ID'
        )->addColumn(
            SourceInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => false,
            ],
            'Source Name'
        )->addColumn(
            SourceInterface::ENABLED,
            Table::TYPE_SMALLINT,
            null,
            [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_DEFAULT => 1,
            ],
            'Defines Is Source Enabled'
        )->addColumn(
            SourceInterface::DESCRIPTION,
            Table::TYPE_TEXT,
            1000,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Description'
        )->addColumn(
            SourceInterface::LATITUDE,
            Table::TYPE_DECIMAL,
            null,
            [
                InstallSchema::OPTION_PRECISION => InstallSchema::LATLON_PRECISION_LAT,
                InstallSchema::OPTION_SCALE => InstallSchema::LATLON_SCALE,
                InstallSchema::OPTION_UNSIGNED => false,
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Latitude'
        )->addColumn(
            SourceInterface::LONGITUDE,
            Table::TYPE_DECIMAL,
            null,
            [
                InstallSchema::OPTION_PRECISION => InstallSchema::LATLON_PRECISION_LON,
                InstallSchema::OPTION_SCALE => InstallSchema::LATLON_SCALE,
                InstallSchema::OPTION_UNSIGNED => false,
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Longitude'
        )->addColumn(
            SourceInterface::PRIORITY,
            Table::TYPE_SMALLINT,
            null,
            [
                InstallSchema::OPTION_NULLABLE => true,
                InstallSchema::OPTION_UNSIGNED => true,
            ],
            'Priority'
        );
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addAddressFields(Table $sourceTable)
    {
        $sourceTable->addColumn(
            SourceInterface::COUNTRY_ID,
            Table::TYPE_TEXT,
            2,
            [
                InstallSchema::OPTION_NULLABLE => false,
            ],
            'Country Id'
        )->addColumn(
            SourceInterface::REGION_ID,
            Table::TYPE_INTEGER,
            null,
            [
                InstallSchema::OPTION_NULLABLE => true,
                InstallSchema::OPTION_UNSIGNED => true,
            ],
            'Region Id'
        )->addColumn(
            SourceInterface::REGION,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Region'
        )->addColumn(
            SourceInterface::CITY,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'City'
        )->addColumn(
            SourceInterface::STREET,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Street'
        )->addColumn(
            SourceInterface::POSTCODE,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => false,
            ],
            'Postcode'
        );
        return $sourceTable;
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addContactInfoFields(Table $sourceTable)
    {
        $sourceTable->addColumn(
            SourceInterface::CONTACT_NAME,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Contact Name'
        )->addColumn(
            SourceInterface::EMAIL,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Email'
        )->addColumn(
            SourceInterface::PHONE,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Phone'
        )->addColumn(
            SourceInterface::FAX,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => true,
            ],
            'Fax'
        );
        return $sourceTable;
    }

    /**
     * @param Table $sourceTable
     * @return Table
     */
    private function addSourceCarrierFields(Table $sourceTable)
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

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createSourceCarrierLinkTable(SchemaSetupInterface $setup)
    {
        $sourceCarrierLinkTable = $setup->getTable(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK);
        $sourceTable = $setup->getTable(InstallSchema::TABLE_NAME_SOURCE);

        return $setup->getConnection()->newTable(
            $sourceCarrierLinkTable
        )->setComment(
            'Inventory Source Carrier Link Table'
        )->addColumn(
            'source_carrier_link_id',
            Table::TYPE_INTEGER,
            null,
            [
                InstallSchema::OPTION_IDENTITY => true,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_PRIMARY => true,
            ],
            'Source Carrier Link ID'
        )->addColumn(
            SourceInterface::SOURCE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
            ],
            'Source ID'
        )->addColumn(
            SourceCarrierLinkInterface::CARRIER_CODE,
            Table::TYPE_TEXT,
            255,
            [
                InstallSchema::OPTION_NULLABLE => false,
            ],
            'Carrier Code'
        )->addColumn(
            'position',
            Table::TYPE_SMALLINT,
            null,
            [
                InstallSchema::OPTION_NULLABLE => true,
                InstallSchema::OPTION_UNSIGNED => true,
            ],
            'Position'
        )->addForeignKey(
            $setup->getFkName(
                $sourceCarrierLinkTable,
                SourceInterface::SOURCE_ID,
                $sourceTable,
                SourceInterface::SOURCE_ID
            ),
            SourceInterface::SOURCE_ID,
            $sourceTable,
            SourceInterface::SOURCE_ID,
            AdapterInterface::FK_ACTION_CASCADE
        );
    }
}
