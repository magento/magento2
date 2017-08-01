<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Search;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Search\CollectionFilter
 *
 * @since 2.0.0
 */
class CollectionFilter implements CollectionFilterInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $catalogConfig;

    /**
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var Visibility
     * @since 2.0.0
     */
    protected $productVisibility;

    /**
     * @param Config $catalogConfig
     * @param StoreManagerInterface $storeManager
     * @param Visibility $productVisibility
     * @since 2.0.0
     */
    public function __construct(
        Config $catalogConfig,
        StoreManagerInterface $storeManager,
        Visibility $productVisibility
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->storeManager = $storeManager;
        $this->productVisibility = $productVisibility;
    }

    /**
     * Filter product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function filter(
        $collection,
        \Magento\Catalog\Model\Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($this->storeManager->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());
    }
}
