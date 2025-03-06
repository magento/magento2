<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogInventory\Model\Config\Source\NotAvailableMessage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;

/**
 * Resolver for ProductInterface quantity
 * Returns the available stock quantity based on cataloginventory/options/not_available_message
 */
class QuantityResolver implements ResolverInterface
{
    /**
     * Configurable product type code
     */
    private const PRODUCT_TYPE_CONFIGURABLE = "configurable";

    /**
     * Scope config path for not_available_message
     */
    private const CONFIG_PATH_NOT_AVAILABLE_MESSAGE = "cataloginventory/options/not_available_message";

    /**
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param StockState $stockState
     * @param ProductStock $productStock
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepositoryInterface,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StockState $stockState,
        private readonly ProductStock $productStock,
    ) {
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?float {

        if ((int) $this->scopeConfig->getValue(
            self::CONFIG_PATH_NOT_AVAILABLE_MESSAGE
        ) === NotAvailableMessage::VALUE_NOT_ENOUGH_ITEMS) {
            return null;
        }

        if (isset($value['cart_item']) && $value['cart_item'] instanceof Item) {
            return $this->productStock->getProductAvailableStock($value['cart_item']);
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];

        if ($product->getTypeId() === self::PRODUCT_TYPE_CONFIGURABLE) {
            $product = $this->productRepositoryInterface->get($product->getSku());
        }
        return $this->stockState->getStockQty($product->getId());
    }
}
