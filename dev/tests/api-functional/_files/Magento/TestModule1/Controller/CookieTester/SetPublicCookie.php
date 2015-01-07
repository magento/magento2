<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Controller\CookieTester;

/**
 */
class SetPublicCookie extends \Magento\TestModule1\Controller\CookieTester
{
    /**
     * Sets a public cookie with data from url parameters
     *
     * @return void
     */
    public function execute()
    {
        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();

        $cookieDomain = $this->getRequest()->getParam('cookie_domain');
        if ($cookieDomain !== null) {
            $publicCookieMetadata->setDomain($cookieDomain);
        }

        $cookiePath = $this->getRequest()->getParam('cookie_path');
        if ($cookiePath !== null) {
            $publicCookieMetadata->setPath($cookiePath);
        }

        $cookieDuration = $this->getRequest()->getParam('cookie_duration');
        if ($cookieDuration !== null) {
            $publicCookieMetadata->setDuration($cookieDuration);
        }

        $httpOnly = $this->getRequest()->getParam('cookie_httponly');
        if ($httpOnly !== null) {
            $publicCookieMetadata->setHttpOnly($httpOnly);
        }

        $secure = $this->getRequest()->getParam('cookie_secure');
        if ($secure !== null) {
            $publicCookieMetadata->setSecure($secure);
        }

        $cookieName = $this->getRequest()->getParam('cookie_name');
        $cookieValue = $this->getRequest()->getParam('cookie_value');
        $this->getCookieManager()->setPublicCookie($cookieName, $cookieValue, $publicCookieMetadata);
    }
}
