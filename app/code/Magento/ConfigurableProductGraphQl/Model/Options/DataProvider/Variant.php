<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Retrieve child products
 */
class Variant
{
    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var StatusFactory
     */
    private $stockStatusFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Configurable $configurableType
     * @param StatusFactory $stockStatusFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Configurable $configurableType,
        StatusFactory $stockStatusFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configurableType = $configurableType;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Load available child products by parent
     *
     * @param ProductInterface $product
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSalableVariantsByParent(ProductInterface $product): array
    {
        $collection = $this->configurableType->getUsedProductCollection($product);
        $collection
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();

        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $stockStatusResource = $this->stockStatusFactory->create();
            $showOutOfStock = $this->scopeConfig->isSetFlag(
                'cataloginventory/options/show_out_of_stock',
                ScopeInterface::SCOPE_STORE
            );
            $stockStatusResource->addStockDataToCollection($collection, !$showOutOfStock);
            $collection->setFlag($stockFlag, !$showOutOfStock);
        }
        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection->getItems() ?? [];
    }
}
