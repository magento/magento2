<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Plugin\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider;
use Magento\Wishlist\Helper\Data;

/**
 * Plugin on Data Provider for frontend ui components (Components are responsible
 * for rendering product on front)
 * This plugin provides allowWishlist setting
 */
class WishlistSettings
{
    /**
     * WishlistSettings constructor.
     * @param Data $helper
     */
    public function __construct(
        private readonly Data $helper
    ) {
    }

    /**
     * Add tax data to result
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        $result['allowWishlist'] = $this->helper->isAllow();

        return $result;
    }
}
