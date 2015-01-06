<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Controller\CookieTester;

/**
 */
class SetSensitiveCookie extends \Magento\TestModule1\Controller\CookieTester
{
    /**
     * Sets a sensitive cookie with data from url parameters
     *
     * @return void
     */
    public function execute()
    {
        $sensitiveCookieMetadata = $this->getCookieMetadataFactory()->createSensitiveCookieMetadata();

        $cookieDomain = $this->getRequest()->getParam('cookie_domain');
        if ($cookieDomain !== null) {
            $sensitiveCookieMetadata->setDomain($cookieDomain);
        }
        $cookiePath = $this->getRequest()->getParam('cookie_domain');
        if ($cookiePath !== null) {
            $sensitiveCookieMetadata->setPath($cookiePath);
        }

        $cookieName = $this->getRequest()->getParam('cookie_name');
        $cookieValue = $this->getRequest()->getParam('cookie_value');
        $this->getCookieManager()->setSensitiveCookie($cookieName, $cookieValue, $sensitiveCookieMetadata);
    }
}
