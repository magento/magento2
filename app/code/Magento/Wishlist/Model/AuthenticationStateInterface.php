<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Wishlist\Model;

/**
 * Interface \Magento\Wishlist\Model\AuthenticationStateInterface
 *
 * @api
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
