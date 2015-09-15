<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Controller\CookieTester;

use \Magento\Framework\App\RequestInterface;

/**
 */
class SetPublicCookie extends \Magento\TestModule1\Controller\CookieTester
{
    /**
     * Sets a public cookie with data from url parameters
     *
     * @return void
     */
    public function execute(RequestInterface $request)
    {
        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();

        $cookieDomain = $request->getParam('cookie_domain');
        if ($cookieDomain !== null) {
            $publicCookieMetadata->setDomain($cookieDomain);
        }

        $cookiePath = $request->getParam('cookie_path');
        if ($cookiePath !== null) {
            $publicCookieMetadata->setPath($cookiePath);
        }

        $cookieDuration = $request->getParam('cookie_duration');
        if ($cookieDuration !== null) {
            $publicCookieMetadata->setDuration($cookieDuration);
        }

        $httpOnly = $request->getParam('cookie_httponly');
        if ($httpOnly !== null) {
            $publicCookieMetadata->setHttpOnly($httpOnly);
        }

        $secure = $request->getParam('cookie_secure');
        if ($secure !== null) {
            $publicCookieMetadata->setSecure($secure);
        }

        $cookieName = $request->getParam('cookie_name');
        $cookieValue = $request->getParam('cookie_value');
        $this->getCookieManager()->setPublicCookie($cookieName, $cookieValue, $publicCookieMetadata);
    }
}
