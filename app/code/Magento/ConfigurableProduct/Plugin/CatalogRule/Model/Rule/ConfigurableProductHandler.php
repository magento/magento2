<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\CatalogRule\Model\Rule;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Add configurable sub products to catalog rule indexer on full reindex
 */
class ConfigurableProductHandler
{
    /** @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable */
    private $configurable;

    /**
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable $configurable
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable $configurable
    ) {
        $this->configurable = $configurable;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $subject
     * @param array $productIds
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMatchingProductIds(
        \Magento\CatalogRule\Model\Rule $subject,
        array $productIds
    ) {
        $configurableProductIds = $this->configurable->getConnection()->fetchCol(
            $this->configurable->getConnection()->select()
            ->from(['t' => $this->configurable->getTable('catalog_product_entity')], 'entity_id')
            ->where('t.type_id = ?', Configurable::TYPE_CODE)
            ->where('t.entity_id IN (?)', array_keys($productIds))
        );

        foreach ($configurableProductIds as $productId) {
            $variationsIds = $this->configurable->getChildrenIds($productId);
            $websitesValid = $productIds[$productId];
            foreach ($variationsIds[0] as $variationId) {
                $productIds[$variationId] = $websitesValid;
            }
            unset($productIds[$productId]);
        }

        return $productIds;
    }
}
