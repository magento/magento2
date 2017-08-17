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
 */
class AuthenticationState implements AuthenticationStateInterface
{
    /**
     * Is authentication enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
}
