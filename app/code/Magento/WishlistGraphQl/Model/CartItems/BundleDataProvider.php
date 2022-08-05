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
 * Data provider for bundlue product cart item request
 */
class BundleDataProvider implements CartItemsRequestDataProviderInterface
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
        if (isset($buyRequest['bundle_option'])) {
            $bundleOptions = $buyRequest['bundle_option'];
            $bundleOptionQty = $buyRequest['bundle_option_qty'];
            foreach ($bundleOptions as $option => $value) {
                $qty = $bundleOptionQty[$option];
                $selected_options[] = $this->uidEncoder->encode("bundle/$option/$value/$qty");
            }
        }

        $cartItems['selected_options'] = $selected_options;
        return $cartItems;
    }
}
