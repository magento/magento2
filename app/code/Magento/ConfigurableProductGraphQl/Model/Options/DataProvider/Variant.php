<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
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
     * @param Configurable $configurableType
     * @param StatusFactory $stockStatusFactory
     */
    public function __construct(
        Configurable $configurableType,
        StatusFactory $stockStatusFactory
    ) {
        $this->configurableType = $configurableType;
        $this->stockStatusFactory = $stockStatusFactory;
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
        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $stockStatusResource = $this->stockStatusFactory->create();
            $stockStatusResource->addStockDataToCollection($collection, true);
            $collection->setFlag($stockFlag, true);
        }
        $collection->clear();

        return $collection->getItems() ?? [];
    }
}
