<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

/**
 * Add new custom layout related attributes.
 */
class UpdateCustomLayoutAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /** @var CategorySetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
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
            Product::ENTITY,
            'custom_layout_update',
            'is_visible',
            false
        );

        $eavSetup->updateAttribute(
            Category::ENTITY,
            'custom_layout_update',
            'is_visible',
            false
        );
    }
}
