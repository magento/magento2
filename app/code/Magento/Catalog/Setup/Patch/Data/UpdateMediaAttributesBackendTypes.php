<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-12-07 11:10:10
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateMediaAttributesBackendTypes.
 */
class UpdateMediaAttributesBackendTypes implements DataPatchInterface, PatchVersionInterface
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
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply(): void
    {
        $mediaBackendType  = 'static';
        $mediaBackendModel = null;

        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
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

    public static function getDependencies()
    {
        return [
            UpdateDefaultAttributeValue::class,
        ];
    }

    public static function getVersion()
    {
        return '2.0.4';
    }

    public function getAliases()
    {
        return [];
    }
}
