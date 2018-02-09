<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Setup\Patch;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class UpdateTierPriceAttribute
 * @package Magento\ConfigurableProduct\Setup\Patch
 */
class UpdateTierPriceAttribute implements DataPatchInterface, PatchVersionInterface
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
     * UpdateTierPriceAttribute constructor.
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
        $relatedProductTypes = explode(
            ',',
            $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'tier_price', 'apply_to')
        );
        $key = array_search(Configurable::TYPE_CODE, $relatedProductTypes);
        if ($key !== false) {
            unset($relatedProductTypes[$key]);
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'tier_price',
                'apply_to',
                implode(',', $relatedProductTypes)
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InstallInitialConfigurableAttributes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.2.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
