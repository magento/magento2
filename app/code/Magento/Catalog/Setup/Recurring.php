<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Catalog recurring setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExternalFKSetup
     */
    protected $externalFKSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ExternalFKSetup $externalFKSetup
     * @param CategorySetupFactory|null $categorySetupFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ExternalFKSetup $externalFKSetup,
        ?CategorySetupFactory $categorySetupFactory = null
    ) {
        $this->metadataPool = $metadataPool;
        $this->externalFKSetup = $externalFKSetup;
        $this->categorySetupFactory = $categorySetupFactory
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(CategorySetupFactory::class);
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $metadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $this->externalFKSetup->install(
            $installer,
            $metadata->getEntityTable(),
            $metadata->getIdentifierField(),
            'catalog_category_product',
            'category_id'
        );

        $this->addCustomLayoutFileAttributes($setup);

        $installer->endSetup();
    }

    /**
     * Add custom layout selector attributes.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addCustomLayoutFileAttributes(SchemaSetupInterface $setup): void
    {
        /** @var CategorySetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $productAttr = $eavSetup->getAttribute(Product::ENTITY, 'custom_layout_update_file');
        if (!$productAttr) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'custom_layout_update_file',
                [
                    'type' => 'varchar',
                    'label' => 'Custom Layout Update',
                    'input' => 'select',
                    'source' => \Magento\Catalog\Model\Product\Attribute\Source\LayoutUpdate::class,
                    'required' => false,
                    'sort_order' => 51,
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Design',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'custom_layout_update',
                'is_visible',
                false
            );
        }

        $categoryAttr = $eavSetup->getAttribute(Category::ENTITY, 'custom_layout_update_file');
        if (!$categoryAttr) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'custom_layout_update_file',
                [
                    'type' => 'varchar',
                    'label' => 'Custom Layout Update',
                    'input' => 'select',
                    'source' => \Magento\Catalog\Model\Category\Attribute\Source\LayoutUpdate::class,
                    'required' => false,
                    'sort_order' => 51,
                    'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Custom Design',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );
            $eavSetup->updateAttribute(
                Category::ENTITY,
                'custom_layout_update',
                'is_visible',
                false
            );
        }
    }
}
