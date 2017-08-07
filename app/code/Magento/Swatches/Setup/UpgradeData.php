<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\Store;
use Magento\Swatches\Model\Swatch;
use Zend_Db;
use Zend_Db_Expr;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ObjectManager;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     * @since 2.1.0
     */
    private $eavSetupFactory;

    /**
     * @var FieldDataConverterFactory
     * @since 2.2.0
     */
    private $fieldDataConverterFactory;

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     * @param FieldDataConverterFactory|null $fieldDataConverterFactory
     * @since 2.1.0
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        FieldDataConverterFactory $fieldDataConverterFactory = null
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory
            ?: ObjectManager::getInstance()->get(FieldDataConverterFactory::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
            $groupId = (int)$eavSetup->getAttributeGroupByCode(
                Product::ENTITY,
                $attributeSetId,
                'image-management',
                'attribute_group_id'
            );
            $eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSetId, $groupId, 'swatch_image');
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->updateAdminTextSwatchValues($setup);
        }
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->convertAddDataToJson($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add fallback for default scope.
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     * @since 2.2.0
     */
    private function updateAdminTextSwatchValues(ModuleDataSetupInterface $setup)
    {
        $storeData = $setup->getConnection()
            ->select()
            ->from($setup->getTable('store'))
            ->where(Store::STORE_ID . "<> ? ", Store::DEFAULT_STORE_ID)
            ->order("sort_order desc")
            ->limit(1)
            ->query(Zend_Db::FETCH_ASSOC)
            ->fetch();

        if (is_array($storeData)) {

            /**
             * update eav_attribute_option_swatch as s
             * left join eav_attribute_option_swatch as ls on ls.option_id = s.option_id and ls.store_id = 1
             * set
             *
             * s.value = ls.value
             * where s.store_id = 0 and s.`type` = 0 and s.value = ""
             */

            /** @var \Magento\Framework\DB\Select $select */
            $select = $setup->getConnection()
                ->select()
                ->joinLeft(
                    ["ls" => $setup->getTable('eav_attribute_option_swatch')],
                    new Zend_Db_Expr("ls.option_id = s.option_id AND ls.store_id = " . $storeData[Store::STORE_ID]),
                    ["value"]
                )
                ->where("s.store_id = ? ", Store::DEFAULT_STORE_ID)
                ->where("s.type = ? ", Swatch::SWATCH_TYPE_TEXTUAL)
                ->where("s.value = ?  or s.value is null", "");

            $setup->getConnection()->query(
                $setup->getConnection()->updateFromSelect(
                    $select,
                    ["s" => $setup->getTable('eav_attribute_option_swatch')]
                )
            );
        }
    }

    /**
     * Convert additional data column from serialized view to JSON for swatch attributes.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function convertAddDataToJson(ModuleDataSetupInterface $setup)
    {
        $fieldConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldConverter->convert(
            $setup->getConnection(),
            $setup->getTable('catalog_eav_attribute'),
            'attribute_id',
            'additional_data'
        );
    }
}
