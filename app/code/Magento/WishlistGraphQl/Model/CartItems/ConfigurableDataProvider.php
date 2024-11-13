<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Model\CartItems;

use Magento\Wishlist\Model\Item;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Data provider for configurable product cart item request
 */
class ConfigurableDataProvider implements CartItemsRequestDataProviderInterface
{
    /** 
     * @var Uid 
     */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(
        Uid $uidEncoder
    ) {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @inheritdoc
     */
    public function execute(Item $wishlistItem, ?string $sku): array
    {
        $buyRequest = $wishlistItem->getBuyRequest();
        $selected_options = [];
        if (isset($buyRequest['super_attribute'])) {
            $superAttributes = $buyRequest['super_attribute'];
            foreach ($superAttributes as $attributeId => $value) {
                $selected_options[] = $this->uidEncoder->encode("configurable/$attributeId/$value");
            }
        }
        $cartItems['selected_options'] = $selected_options;
        return $cartItems;
    }
}
