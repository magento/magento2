<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class ProductMatrixProvider
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param Image $image
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(Image $image, StockRegistryInterface $stockRegistry)
    {
        $this->image = $image;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param ProductInterface $product
     * @param array $variationOptions
     *
     * @return array
     */
    public function get(ProductInterface $product, array $variationOptions): array
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $quantity = $stockItem->getQty();

        return [
            'productId' => $product->getId(),
            'images' => [
                'preview' => $this->image->init($product, 'product_thumbnail_image')->getUrl()
            ],
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'quantity' => $quantity,
            'price' => $product->getPrice(),
            'options' => $variationOptions,
            'weight' => $product->getWeight(),
            'status' => $product->getStatus(),
        ];
    }
}
