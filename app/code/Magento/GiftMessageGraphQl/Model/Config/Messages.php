<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Config;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Messages
{
    public const XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS = 'sales/gift_options/allow_items';

    /**
     * Messages Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Check if gift message allowed for specified entity
     *
     * @param string $type
     * @param DataObject $entity
     * @param int|Store|null $store
     * @return bool
     */
    public function isMessagesAllowed(
        string $type,
        DataObject $entity,
        int|Store|null $store = null
    ): bool {
        if ($type === 'items' && !empty($entity->getAllItems())) {
            foreach ($entity->getAllItems() as $item) {
                if (!$item->getParentItem() && $this->isMessagesAllowed('item', $item, $store)) {
                    return true;
                }
            }
        }

        return $this->isGiftMessageAllowedForProduct(
            $entity instanceof QuoteItem ? $entity->getProduct()->getGiftMessageAvailable()
                : ($entity instanceof OrderItem ? $entity->getGiftMessageAvailable() : null),
            $store
        );
    }

    /**
     * Check if gift message allowed for specified product
     *
     * @param string|null $productConfig
     * @param Store|int|null $store
     * @return bool
     */
    public function isGiftMessageAllowedForProduct(
        ?string $productConfig,
        Store|int|null $store
    ): bool {
        return in_array($productConfig, [null, '', Boolean::VALUE_USE_CONFIG])
            ? (bool) $this->scopeConfig->getValue(
                self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
                ScopeInterface::SCOPE_STORE,
                $store
            ) : (bool) $productConfig;
    }
}
