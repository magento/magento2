<?php

namespace Magento\Inventory\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class InstallSchema
 * @package Magento\Inventory\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     *
     */
    const TABLE_NAME_SOURCE = 'inventory_source';
    const TABLE_NAME_SOURCE_CARRIER_LINK = 'inventory_source_carrier_link';


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

    /**
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

            $options = [
                InstallSchema::OPTION_IDENTITY => true,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_PRIMARY => true
            ];
            $table->addColumn(SourceInterface::SOURCE_ID, Table::TYPE_INTEGER, null, $options, 'Source ID');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::NAME, Table::TYPE_TEXT, null, $options, 'Source Name');


            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::CONTACT_NAME, Table::TYPE_TEXT, null, $options, 'Contact Name');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::EMAIL, Table::TYPE_TEXT, null, $options, 'Email');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_DEFAULT => 1
            ];
            $table->addColumn(SourceInterface::IS_ACTIVE,
                Table::TYPE_SMALLINT,
                null,
                $options,
                'Defines Is Source Active');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::DESCRIPTION, Table::TYPE_TEXT, null, $options, 'Description');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::LATITUDE, Table::TYPE_TEXT, null, $options, 'Latitude');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::LONGITUDE, Table::TYPE_TEXT, null, $options, 'Longitude');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_DEFAULT => 1
            ];

            $table->addColumn(SourceInterface::COUNTRY_ID, Table::TYPE_SMALLINT, null, $options, 'Country Id');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_DEFAULT => 1
            ];

            $table->addColumn(SourceInterface::REGION_ID, Table::TYPE_SMALLINT, null, $options, 'Region Id');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::REGION, Table::TYPE_TEXT, null, $options, 'Region');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::CITY, Table::TYPE_TEXT, null, $options, 'City');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::STREET, Table::TYPE_TEXT, null, $options, 'Street');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::POSTCODE, Table::TYPE_TEXT, null, $options, 'Postcode');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::PHONE, Table::TYPE_TEXT, null, $options, 'Phone');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];
            $table->addColumn(SourceInterface::FAX, Table::TYPE_TEXT, null, $options, 'Fax');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_DEFAULT => 1
            ];

            $table->addColumn(SourceInterface::PRIORITY, Table::TYPE_SMALLINT, null, $options, 'Priority');


            $table->setComment('Inventory Source Entity Table')->setOption('type', 'InnoDB')->setOption('charset',
                'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableNamePipelineEntity = $installer->getTable(InstallSchema::TABLE_NAME_SOURCE);
        if (!$installer->getConnection()->isTableExists($tableNamePipelineEntity)) {

            $table = $installer->getConnection()->newTable($tableNameSourceEntity);

            $options = [
                InstallSchema::OPTION_IDENTITY => true,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_NULLABLE => false,
            ];
            $table->addColumn('source_carrier_link_id',
                Table::TYPE_INTEGER,
                null,
                $options,
                'Source ID'
            );

            $options = [
                InstallSchema::OPTION_IDENTITY => true,
                InstallSchema::OPTION_UNSIGNED => true,
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_PRIMARY => true
            ];
            $table->addColumn(SourceInterface::SOURCE_ID, Table::TYPE_INTEGER, null, $options, 'Source ID');

            $options = [
                InstallSchema::OPTION_NULLABLE => false,
                InstallSchema::OPTION_DEFAULT => ''
            ];

            $table->addColumn(

                Table::TYPE_TEXT,
                null,
                $options,
                'Source Name'
            );

        }


        $setup->endSetup();
    }
}
