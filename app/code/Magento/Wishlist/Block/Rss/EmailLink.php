<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist RSS URL to Email Block
 */
namespace Magento\Wishlist\Block\Rss;

/**
 * Class EmailLink
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class EmailLink extends Link
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'rss/email.phtml';

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getLinkParams()
    {
        $params = parent::getLinkParams();
        $wishlist = $this->wishlistHelper->getWishlist();
        $sharingCode = $wishlist->getSharingCode();
        if ($sharingCode) {
            $params['sharing_code'] = $sharingCode;
        }
        return $params;
    }
}
