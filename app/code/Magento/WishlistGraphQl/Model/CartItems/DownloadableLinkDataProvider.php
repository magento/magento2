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
 * Data provider for downloadable product links cart item request
 */
class DownloadableLinkDataProvider implements CartItemsRequestDataProviderInterface
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
        $links = isset($buyRequest['links']) ? $buyRequest['links'] : [];
        $selectedOptions = [];
        $cartItems = [];
        foreach ($links as $linkId) {
            $selectedOptions[] = $this->uidEncoder->encode("downloadable/$linkId");
        }
        $cartItems['selected_options'] = $selectedOptions;
        return $cartItems;
    }
}
