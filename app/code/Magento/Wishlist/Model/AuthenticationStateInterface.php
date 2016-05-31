<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

interface AuthenticationStateInterface
{
    /**
     * Is authentication enabled
     *
     * @return bool
     */
    public function isEnabled();
}
