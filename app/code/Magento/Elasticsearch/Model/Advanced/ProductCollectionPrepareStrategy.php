<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Advanced;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Config;
use Magento\CatalogSearch\Model\Advanced\ProductCollectionPrepareStrategyInterface;

/**
 * Strategy interface for preparing product collection.
 */
class ProductCollectionPrepareStrategy implements ProductCollectionPrepareStrategyInterface
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @param Config $catalogConfig
     */
    public function __construct(
        Config $catalogConfig
    ) {
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * @inheritdoc
     */
    public function prepare(Collection $collection)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addTaxPercents();
    }
}
