<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Controller\CookieTester;

use \Magento\Framework\App\RequestInterface;

/**
 */
class SetSensitiveCookie extends \Magento\TestModule1\Controller\CookieTester
{
    /**
     * Sets a sensitive cookie with data from url parameters
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $sensitiveCookieMetadata = $this->getCookieMetadataFactory()->createSensitiveCookieMetadata();

        $cookieDomain = $this->request->getParam('cookie_domain');
        if ($cookieDomain !== null) {
            $sensitiveCookieMetadata->setDomain($cookieDomain);
        }
        $cookiePath = $this->request->getParam('cookie_domain');
        if ($cookiePath !== null) {
            $sensitiveCookieMetadata->setPath($cookiePath);
        }

        $cookieName = $this->request->getParam('cookie_name');
        $cookieValue = $this->request->getParam('cookie_value');
        $this->getCookieManager()->setSensitiveCookie($cookieName, $cookieValue, $sensitiveCookieMetadata);
        return $this->_response;
    }
}
