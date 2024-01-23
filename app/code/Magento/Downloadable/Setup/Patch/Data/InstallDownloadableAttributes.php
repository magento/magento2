<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InstallDownloadableAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallDownloadableAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /**
         * Add attributes to the eav/attribute table
         */
        $eavSetup->addAttribute(
            Product::ENTITY,
            'links_purchased_separately',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Links can be purchased separately',
                'input' => '',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'downloadable',
                'used_in_product_listing' => true
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'samples_title',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Samples title',
                'input' => '',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'downloadable'
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'links_title',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Links title',
                'input' => '',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'downloadable'
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'links_exist',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => '',
                'input' => '',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'downloadable',
                'used_in_product_listing' => 1
            ]
        );
        $fieldList = [
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'minimal_price',
            'cost',
            'tier_price',
            'weight',
        ];
        // make these attributes applicable to downloadable products
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to') ?? ''
            );
            if (!in_array('downloadable', $applyTo)) {
                $applyTo[] = 'downloadable';
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
