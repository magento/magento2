<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Update manufacturer attribute if it's presented in system.
 */
class UpdateManufacturerAttribute implements DataPatchInterface, PatchVersionInterface
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

        if ($manufacturer = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'manufacturer',
            'apply_to'
        )) {
            $relatedProductTypes = explode(
                ',',
                $manufacturer
            );

            if (!in_array(Configurable::TYPE_CODE, $relatedProductTypes)) {
                $relatedProductTypes[] = Configurable::TYPE_CODE;
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'manufacturer',
                    'apply_to',
                    implode(',', $relatedProductTypes)
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InstallInitialConfigurableAttributes::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.2.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
