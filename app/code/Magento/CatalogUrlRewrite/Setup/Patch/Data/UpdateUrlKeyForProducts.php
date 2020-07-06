<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Url;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update url_key all products.
 */
class UpdateUrlKeyForProducts implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Url
     */
    private $urlProduct;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Url $urlProduct
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Url $urlProduct
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);
        $this->urlProduct = $urlProduct;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $productTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $table = $this->moduleDataSetup->getTable('catalog_product_entity_varchar');
        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $table,
            ['value_id', 'value']
        )->where(
            'attribute_id = ?',
            $this->eavSetup->getAttributeId($productTypeId, 'url_key')
        );

        $result = $this->moduleDataSetup->getConnection()->fetchAll($select);
        foreach ($result as $key => $item) {
            $result[$key]['value'] = $this->urlProduct->formatUrlKey($item['value']);
        }

        foreach (array_chunk($result, 500, true) as $pathResult) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate($table, $pathResult, ['value']);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getVersion()
    {
        return "2.4.0";
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
    public function getAliases()
    {
        return [];
    }
}
