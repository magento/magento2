<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Customer redirect cookie manager interface
 *
 * @api
 */
interface RedirectCookieManagerInterface
{
    /**
     * Get redirect route from cookie for case of successful login/registration
     *
     * @return null|string
     */
    public function getRedirectCookie();

    /**
     * Save redirect route to cookie for case of successful login/registration
     *
     * @param string $route
     * @param StoreInterface $store
     * @return void
     */
    public function setRedirectCookie($route, StoreInterface $store);

    /**
     * Clear cookie with requested route
     *
     * @param StoreInterface $store
     * @return void
     */
    public function clearRedirectCookie(StoreInterface $store);
}
