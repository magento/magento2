<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;

/**
 * Resolver for not_available_message
 * Returns the configured response to the shopper if the requested quantity is not available
 */
class NotAvailableMessageResolver implements ResolverInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductStock $productStock
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ProductStock $productStock
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        if ($this->productStock->isProductAvailable($cartItem)) {
            return null;
        }

        if ((int) $this->scopeConfig->getValue('cataloginventory/options/not_available_message') === 1) {
            $requiredItemQty = ($cartItem->getQtyToAdd() ?? $cartItem->getQty()) + ($cartItem->getPreviousQty() ?? 0);
            return sprintf(
                'Only %s of %s available',
                (string) $this->productStock->getProductSaleableQty($cartItem),
                (string) $requiredItemQty
            );
        }

        return 'Not enough items for sale';
    }
}
