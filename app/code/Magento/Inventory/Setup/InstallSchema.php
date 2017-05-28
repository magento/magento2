<?php

namespace Magento\Inventory\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

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
    const LATLON_PRECISION = 10;
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
     * Generates needed database structure for source and sourcecarrierlink implementation from this module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableNameSourceEntity = $installer->getTable(InstallSchema::TABLE_NAME_SOURCE);
        if (!$installer->getConnection()->isTableExists($tableNameSourceEntity)) {
            $table = $installer->getConnection()->newTable($tableNameSourceEntity);

            $table->addColumn(
                SourceInterface::SOURCE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    InstallSchema::OPTION_IDENTITY => true,
                    InstallSchema::OPTION_UNSIGNED => true,
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_PRIMARY => true
                ],
                'Source ID'
            )->addColumn(
                SourceInterface::NAME,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Source Name'
            )->addColumn(
                SourceInterface::CONTACT_NAME,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Contact Name'
            )->addColumn(
                SourceInterface::EMAIL,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Email'
            )->addColumn(
                SourceInterface::IS_ACTIVE,
                Table::TYPE_SMALLINT,
                null,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_UNSIGNED => true,
                    InstallSchema::OPTION_DEFAULT => 1
                ],
                'Defines Is Source Active'
            )->addColumn(
                SourceInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Description'
            )->addColumn(
                SourceInterface::LATITUDE,
                Table::TYPE_DECIMAL,
                null,
                [
                    InstallSchema::OPTION_PRECISION => InstallSchema::LATLON_PRECISION,
                    InstallSchema::OPTION_SCALE => InstallSchema::LATLON_SCALE,
                    InstallSchema::OPTION_NULLABLE => true
                ],
                'Latitude'
            )->addColumn(
                SourceInterface::LONGITUDE,
                Table::TYPE_DECIMAL,
                null,
                [
                    InstallSchema::OPTION_PRECISION => InstallSchema::LATLON_PRECISION,
                    InstallSchema::OPTION_SCALE => InstallSchema::LATLON_SCALE,
                    InstallSchema::OPTION_NULLABLE => true
                ],
                'Longitude'
            )->addColumn(
                SourceInterface::COUNTRY_ID,
                Table::TYPE_TEXT,
                30,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Country Id'
            )->addColumn(
                SourceInterface::REGION_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    InstallSchema::OPTION_NULLABLE => true,
                    InstallSchema::OPTION_UNSIGNED => true
                ],
                'Region Id'
            )->addColumn(
                SourceInterface::REGION,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Region'
            )->addColumn(
                SourceInterface::CITY,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'City'
            )->addColumn(
                SourceInterface::STREET,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Street'
            )->addColumn(
                SourceInterface::POSTCODE,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Postcode'
            )->addColumn(
                SourceInterface::PHONE,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Phone'
            )->addColumn(
                SourceInterface::FAX,
                Table::TYPE_TEXT,
                255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Fax'
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

            $table->setComment('Inventory Source Entity Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableNameCarrierLinkEntity = $installer->getTable(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK);
        if (!$installer->getConnection()->isTableExists($tableNameCarrierLinkEntity)) {

            $table = $installer->getConnection()->newTable($tableNameCarrierLinkEntity);
            $table->addColumn(
                'source_carrier_link_id',
                Table::TYPE_INTEGER,
                null,
                [
                    InstallSchema::OPTION_IDENTITY => true,
                    InstallSchema::OPTION_UNSIGNED => true,
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_PRIMARY => true
                ],
                'Source Carrier Link ID'
            )->addColumn(
                SourceInterface::SOURCE_ID,
                Table::TYPE_INTEGER, null,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_UNSIGNED => true,
                ],
                'Source ID'
            )->addColumn(
                SourceCarrierLinkInterface::CARRIER_CODE,
                Table::TYPE_TEXT, 255,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_DEFAULT => ''
                ],
                'Carrier Code'
            )->addColumn(
                'position', Table::TYPE_SMALLINT,
                null,
                [
                    InstallSchema::OPTION_NULLABLE => false,
                    InstallSchema::OPTION_UNSIGNED => true,
                ],
                'Position'
            );

            // Add foreign key for Pipeline ID field
            $foreignKeyName = $installer->getConnection()->getForeignKeyName(
                $tableNameCarrierLinkEntity,
                SourceInterface::SOURCE_ID,
                $tableNameCarrierLinkEntity,
                SourceInterface::SOURCE_ID
            );
            $table->addForeignKey(
                $foreignKeyName,
                SourceInterface::SOURCE_ID,
                $tableNameSourceEntity,
                SourceInterface::SOURCE_ID,
                Table::ACTION_CASCADE
            );

            $table->setComment('Inventory Source Carrier Link Entity Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
