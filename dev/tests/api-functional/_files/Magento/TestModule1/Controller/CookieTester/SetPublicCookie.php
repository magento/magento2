<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();

        $cookieDomain = $this->request->getParam('cookie_domain');
        if ($cookieDomain !== null) {
            $publicCookieMetadata->setDomain($cookieDomain);
        }

        $cookiePath = $this->request->getParam('cookie_path');
        if ($cookiePath !== null) {
            $publicCookieMetadata->setPath($cookiePath);
        }

        $cookieDuration = $this->request->getParam('cookie_duration');
        if ($cookieDuration !== null) {
            $publicCookieMetadata->setDuration($cookieDuration);
        }

        $httpOnly = $this->request->getParam('cookie_httponly');
        if ($httpOnly !== null) {
            $publicCookieMetadata->setHttpOnly($httpOnly);
        }

        $secure = $this->request->getParam('cookie_secure');
        if ($secure !== null) {
            $publicCookieMetadata->setSecure($secure);
        }

        $cookieName = $this->request->getParam('cookie_name');
        $cookieValue = $this->request->getParam('cookie_value');
        $this->getCookieManager()->setPublicCookie($cookieName, $cookieValue, $publicCookieMetadata);
        return $this->_response;
    }
}
