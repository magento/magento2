<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Controller\CookieTester;

use \Magento\Framework\App\RequestInterface;

/**
 * Controller to test deletion of a cookie
 */
class DeleteCookie extends \Magento\TestModule1\Controller\CookieTester
{
    /**
     *
     * @return void
     */
    public function execute(RequestInterface $request)
    {
        $cookieName = $request->getParam('cookie_name');
        $this->getCookieManager()->deleteCookie($cookieName);
    }
}
