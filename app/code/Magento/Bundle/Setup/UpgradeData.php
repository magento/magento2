<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * UpgradeData constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
            $eavSetup->addAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetId,
                'Bundle Items',
                16
            );

            $this->upgradePriceType($eavSetup);
            $this->upgradeSkuType($eavSetup);
            $this->upgradeWeightType($eavSetup);
            $this->upgradeShipmentType($eavSetup);
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            // Updating data of the 'catalog_product_bundle_option_value' table.
            $tableName = $setup->getTable('catalog_product_bundle_option_value');

            $select = $setup->getConnection()->select()
                ->from(
                    ['values' => $tableName],
                    ['value_id']
                )->joinLeft(
                    ['options' => $setup->getTable('catalog_product_bundle_option')],
                    'values.option_id = options.option_id',
                    ['parent_product_id' => 'parent_id']
                );

            $setup->getConnection()->query(
                $setup->getConnection()->insertFromSelect(
                    $select,
                    $tableName,
                    ['value_id', 'parent_product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );

            // Updating data of the 'catalog_product_bundle_selection_price' table.
            $tableName = $setup->getTable('catalog_product_bundle_selection_price');
            $tmpTableName = $setup->getTable('catalog_product_bundle_selection_price_tmp');

            $existingForeignKeys = $setup->getConnection()->getForeignKeys($tableName);

            foreach ($existingForeignKeys as $key) {
                $setup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
            }

            $setup->getConnection()->createTable(
                $setup->getConnection()->createTableByDdl($tableName, $tmpTableName)
            );

            foreach ($existingForeignKeys as $key) {
                $setup->getConnection()->addForeignKey(
                    $key['FK_NAME'],
                    $key['TABLE_NAME'],
                    $key['COLUMN_NAME'],
                    $key['REF_TABLE_NAME'],
                    $key['REF_COLUMN_NAME'],
                    $key['ON_DELETE']
                );
            }

            $setup->getConnection()->query(
                $setup->getConnection()->insertFromSelect(
                    $setup->getConnection()->select()->from($tableName),
                    $tmpTableName
                )
            );

            $setup->getConnection()->truncateTable($tableName);

            $columnsToSelect = [];

            foreach ($setup->getConnection()->describeTable($tmpTableName) as $column) {
                $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';

                $columnsToSelect[] = $alias . $column['COLUMN_NAME'];
            }

            $select = $setup->getConnection()->select()
                ->from(
                    ['prices' => $tmpTableName],
                    []
                )->joinLeft(
                    ['selections' => $setup->getTable('catalog_product_bundle_selection')],
                    'prices.selection_id = selections.selection_id',
                    []
                )->columns($columnsToSelect);

            $setup->getConnection()->query(
                $setup->getConnection()->insertFromSelect($select, $tableName)
            );

            $setup->getConnection()->dropTable($tmpTableName);
        }

        $setup->endSetup();
    }

    /**
     * Upgrade Dynamic Price attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradePriceType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_input',
            'boolean',
            31
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_label',
            'Dynamic Price'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'price_type', 'default_value', 0);
    }

    /**
     * Upgrade Dynamic Sku attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeSkuType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_input',
            'boolean',
            21
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_label',
            'Dynamic SKU'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'is_visible', 1);
    }

    /**
     * Upgrade Dynamic Weight attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeWeightType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_input',
            'boolean',
            71
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_label',
            'Dynamic Weight'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'is_visible', 1);
    }

    /**
     * Upgrade Ship Bundle Items attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeShipmentType(EavSetup $eavSetup)
    {
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $eavSetup->addAttributeToGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            'Bundle Items',
            'shipment_type',
            1
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_input',
            'select'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_label',
            'Ship Bundle Items'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'source_model',
            \Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type::class
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'is_visible', 1);
    }
}
