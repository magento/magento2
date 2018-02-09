<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateMediaAttributesBackendTypes
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateMediaAttributesBackendTypes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $mediaBackendType = 'static';
        $mediaBackendModel = null;
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $categorySetup->updateAttribute(
            'catalog_product',
            'media_gallery',
            'backend_type',
            $mediaBackendType
        );
        $categorySetup->updateAttribute(
            'catalog_product',
            'media_gallery',
            'backend_model',
            $mediaBackendModel
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateDefaultAttributeValue::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.4';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
