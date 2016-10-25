<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRuleConfigurable\Plugin\ConfigurableProduct\Model\ResourceModel;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;

class AddCatalogRulePrice
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\CollectionFactory
     */
    private $catalogRuleCollectionFactory;

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Product\CollectionFactory $catalogRuleCollectionFactory
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Product\CollectionFactory $catalogRuleCollectionFactory
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
