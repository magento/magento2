<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRuleConfigurable\Plugin\ConfigurableProduct\Model\ResourceModel;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;

/**
 * Class \Magento\CatalogRuleConfigurable\Plugin\ConfigurableProduct\Model\ResourceModel\AddCatalogRulePrice
 *
 */
class AddCatalogRulePrice
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessorFactory
     */
    private $catalogRuleCollectionFactory;

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessorFactory $catalogRuleCollectionFactory
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessorFactory $catalogRuleCollectionFactory
    ) {
        $this->catalogRuleCollectionFactory = $catalogRuleCollectionFactory;
    }

    /**
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        $this->catalogRuleCollectionFactory
            ->create()
            ->addPriceData($productCollection);

        return [$printQuery, $logQuery];
    }
}
