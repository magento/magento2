<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @inheritDoc
 */
class RecurringData implements InstallDataInterface
{

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addCustomLayoutFileAttributes($setup);
    }

    /**
     * Add custom layout selector attributes.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function addCustomLayoutFileAttributes(ModuleDataSetupInterface $setup)
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
