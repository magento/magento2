<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Setup;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $relatedProductTypes = $this->getRelatedProductTypes('tier_price', $eavSetup);
            $key = array_search(Configurable::TYPE_CODE, $relatedProductTypes);
            if ($key !== false) {
                unset($relatedProductTypes[$key]);
                $this->updateRelatedProductTypes('tier_price', $relatedProductTypes, $eavSetup);
            }
        }

        if (version_compare($context->getVersion(), '2.2.1') < 0) {
            $relatedProductTypes = $this->getRelatedProductTypes('manufacturer', $eavSetup);
            if (!in_array(Configurable::TYPE_CODE, $relatedProductTypes)) {
                $relatedProductTypes[] = Configurable::TYPE_CODE;
                $this->updateRelatedProductTypes('manufacturer', $relatedProductTypes, $eavSetup);
            }
        }

        if (version_compare($context->getVersion(), '2.2.2', '<')) {
            $this->upgradeQuoteItemPrice($setup);
        }

        $setup->endSetup();
    }

    /**
     * Get related product types for attribute.
     *
     * @param string $attributeId
     * @param EavSetup $eavSetup
     * @return array
     */
    private function getRelatedProductTypes(string $attributeId, EavSetup $eavSetup)
    {
        return explode(
            ',',
            $eavSetup->getAttribute(Product::ENTITY, $attributeId, 'apply_to')
        );
    }

    /**
     * Update related product types for attribute.
     *
     * @param string $attributeId
     * @param array $relatedProductTypes
     * @param EavSetup $eavSetup
     * @return void
     */
    private function updateRelatedProductTypes(string $attributeId, array $relatedProductTypes, EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            Product::ENTITY,
            $attributeId,
            'apply_to',
            implode(',', $relatedProductTypes)
        );
    }

    /**
     * Update 'price' value for quote items without price of configurable products subproducts.
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeQuoteItemPrice(ModuleDataSetupInterface $setup)
    {
        $connectionName = 'checkout';
        $connection = $setup->getConnection($connectionName);
        $quoteItemTable = $setup->getTable('quote_item', $connectionName);

        $select = $connection->select();
        $select->joinLeft(
            ['qi2' => $quoteItemTable],
            'qi1.parent_item_id = qi2.item_id',
            ['price']
        )->where(
            'qi1.price = 0'
            . ' AND qi1.parent_item_id IS NOT NULL'
            . ' AND qi2.product_type = "' . Configurable::TYPE_CODE . '"'
        );
        $updateQuoteItem = $connection->updateFromSelect(
            $select,
            ['qi1' => $quoteItemTable]
        );
        $connection->query($updateQuoteItem);
    }
}
