<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
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
