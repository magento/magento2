<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

/**
 * Interface \Magento\Wishlist\Model\AuthenticationStateInterface
 *
 * @since 2.0.0
 */
interface AuthenticationStateInterface
{
    /**
     * Is authentication enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled();
}
