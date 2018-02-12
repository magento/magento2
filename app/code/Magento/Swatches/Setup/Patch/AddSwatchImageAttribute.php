<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup\Patch;

use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class AddSwatchImageAttribute
 * @package Magento\Swatches\Setup\Patch
 */
class AddSwatchImageAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    public function __construct(
        ResourceConnection $resourceConnection,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        /**
         * Install eav entity types to the eav/entity_type table
         */
        $eavSetup->addAttribute(
            'catalog_product',
            'swatch_image',
            [
                'type' => 'varchar',
                'label' => 'Swatch',
                'input' => 'media_image',
                'frontend' => Image::class,
                'required' => false,
                'sort_order' => 3,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}