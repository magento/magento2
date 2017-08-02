<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Plugin\Ui\DataProvider;

use Magento\Wishlist\Helper\Data;

/**
 * Plugin on Data Provider for frontend ui components (Components are responsible
 * for rendering product on front)
 * This plugin provides allowWishlist setting
 * @since 2.2.0
 */
class WishlistSettings
{
    /**
     * @var Data
     * @since 2.2.0
     */
    private $helper;

    /**
     * WishlistSettings constructor.
     * @param Data $helper
     * @since 2.2.0
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGetData(\Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider $subject, $result)
    {
        $result['allowWishlist'] = $this->helper->isAllow();

        return $result;
    }
}
