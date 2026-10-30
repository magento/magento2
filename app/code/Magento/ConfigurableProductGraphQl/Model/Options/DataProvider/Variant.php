<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
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
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @param Configurable $configurableType
     * @param StatusFactory $stockStatusFactory
     * @param StockConfigurationInterface|null $stockConfig
     */
    public function __construct(
        Configurable $configurableType,
        StatusFactory $stockStatusFactory,
        ?StockConfigurationInterface $stockConfig = null
    ) {
        $this->configurableType = $configurableType;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockConfig = $stockConfig ?? ObjectManager::getInstance()->get(StockConfigurationInterface::class);
    }

    /**
     * Load available child products by parent
     *
     * Out of stock children are included when "Display Out of Stock Products" is enabled for the current store.
     *
     * @param ProductInterface $product
     * @return ProductInterface[]
     * @throws LocalizedException
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
            $stockStatusResource->addStockDataToCollection($collection, !$this->stockConfig->isShowOutOfStock());
            $collection->setFlag($stockFlag, true);
        }
        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection->getItems() ?? [];
    }
}
