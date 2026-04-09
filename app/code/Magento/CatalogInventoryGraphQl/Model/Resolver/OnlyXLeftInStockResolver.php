<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class OnlyXLeftInStockResolver implements ResolverInterface
{
    /**
     * Configurable product type code
     */
    private const PRODUCT_TYPE_CONFIGURABLE = "configurable";

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StockRegistryInterface $stockRegistry,
        private readonly ProductRepositoryInterface $productRepositoryInterface
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $product = $value['model'];
        if ($product->getTypeId() === self::PRODUCT_TYPE_CONFIGURABLE) {
            $variant = $this->productRepositoryInterface->get($product->getSku());
            return $this->getOnlyXLeftQty($variant);
        }
        return $this->getOnlyXLeftQty($product);
    }

    /**
     * Get product qty left when "Catalog > Inventory > Stock Options > Only X left Threshold" is greater than 0
     *
     * @param ProductInterface $product
     *
     * @return null|float
     */
    private function getOnlyXLeftQty(ProductInterface $product): ?float
    {
        $thresholdQty = (float)$this->scopeConfig->getValue(
            Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            ScopeInterface::SCOPE_STORE
        );
        if ($thresholdQty === 0.0) {
            return null;
        }

        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        $stockCurrentQty = $this->stockRegistry->getStockStatus(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getQty();

        $stockLeft = $stockCurrentQty - $stockItem->getMinQty();

        if ($stockCurrentQty >= 0 && $stockLeft <= $thresholdQty) {
            return (float)$stockCurrentQty;
        }

        return null;
    }
}
