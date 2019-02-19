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
 */
interface AuthenticationStateInterface
{
    /**
     * Is authentication enabled
     *
     * @return bool
     */
    public function isEnabled();
}
