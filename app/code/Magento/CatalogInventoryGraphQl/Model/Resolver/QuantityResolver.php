<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Config\Source\NotAvailableMessage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;
use Magento\Store\Model\ScopeInterface;

/**
 * Resolves the salable quantity for a product returns null
 * when unavailable and store shows "Not enough items" message.
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
     * QuantityResolver Constructor
     *
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductStock $productStock
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepositoryInterface,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ProductStock $productStock
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

        if (isset($value['cart_item']) && $value['cart_item'] instanceof Item) {
            return $this->productStock->getSaleableQtyByCartItem($value['cart_item'], null);
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];

        if ($product->getTypeId() === self::PRODUCT_TYPE_CONFIGURABLE) {
            $product = $this->productRepositoryInterface->get($product->getSku());
        }

        if (!$this->productStock->checkIfProductIsAvailable($product)
            && (int) $this->scopeConfig->getValue(
                self::CONFIG_PATH_NOT_AVAILABLE_MESSAGE,
                ScopeInterface::SCOPE_STORE
            ) === NotAvailableMessage::VALUE_NOT_ENOUGH_ITEMS
        ) {
            return null;
        }

        return $this->productStock->getSaleableQty($product, null);
    }
}
