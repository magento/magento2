<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Remove default value from links exist attribute.
 */
class UpdateLinksExistDefaultAttributeValue implements DataPatchInterface, PatchVersionInterface
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
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        // remove default value
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'links_exist',
            'default_value',
            null
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [InstallDownloadableAttributes::class];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.3';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
