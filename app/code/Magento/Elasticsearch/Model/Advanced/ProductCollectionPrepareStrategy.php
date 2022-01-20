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
use Magento\Framework\App\ObjectManager;

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
     * @param Visibility|null $catalogProductVisibility
     */
    public function __construct(
        Config $catalogConfig,
        Visibility $catalogProductVisibility = null
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->catalogProductVisibility = $catalogProductVisibility
            ?? ObjectManager::getInstance()->get(Visibility::class);
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
