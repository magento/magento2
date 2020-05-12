<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Advanced;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Strategy interface for preparing product collection.
 * @deprecated See elastic search strategy
 */
class ProductCollectionPrepareStrategy implements ProductCollectionPrepareStrategyInterface
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @param Config $catalogConfig
     * @param StoreManagerInterface $storeManager
     * @param Visibility $catalogProductVisibility
     */
    public function __construct(
        Config $catalogConfig,
        StoreManagerInterface $storeManager,
        Visibility $catalogProductVisibility
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->storeManager = $storeManager;
        $this->catalogProductVisibility = $catalogProductVisibility;
    }

    /**
     * @inheritdoc
     */
    public function prepare(Collection $collection)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($this->storeManager->getStore())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());
    }
}
