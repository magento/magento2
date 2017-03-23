<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist RSS URL to Email Block
 */
namespace Magento\Wishlist\Block\Rss;

/**
 * Class EmailLink
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class EmailLink extends Link
{
    /**
     * @var string
     */
    protected $_template = 'rss/email.phtml';

    /**
     * @return string
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
