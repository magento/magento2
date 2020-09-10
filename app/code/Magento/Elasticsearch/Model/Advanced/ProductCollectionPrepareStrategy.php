<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Advanced;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
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
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @param Config $catalogConfig
     * @param Visibility $catalogProductVisibility
     */
    public function __construct(
        Config $catalogConfig,
        Visibility $catalogProductVisibility
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->catalogProductVisibility = $catalogProductVisibility;
    }

    /**
     * @inheritdoc
     */
    public function prepare(Collection $collection)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());
    }
}
