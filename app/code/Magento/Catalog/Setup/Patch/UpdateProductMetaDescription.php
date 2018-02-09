<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateProductMetaDescription
 *
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateProductMetaDescription implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     * @param EavSetupFactory $eavSetupFactory
     */
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
        $eavSetup = $this->eavSetupFactory->create(['resourceConnection' => $this->resourceConnection]);

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'meta_description',
            [
                'note' => 'Maximum 255 chars. Meta Description should optimally be between 150-160 characters'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateProductAttributes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.7';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
