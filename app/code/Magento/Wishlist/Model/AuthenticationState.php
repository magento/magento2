<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

/**
 * Class \Magento\Wishlist\Model\AuthenticationState
 *
 * @since 2.0.0
 */
class AuthenticationState implements AuthenticationStateInterface
{
    /**
     * Is authentication enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return true;
    }
}
