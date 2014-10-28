<?php
/**
 * Response redirector
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\App\Response;

class Redirect implements \Magento\Framework\App\Response\RedirectInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Encryption\UrlCoder
     */
    protected $_urlCoder;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var bool
     */
    protected $_canUseSessionIdInParam;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param bool $canUseSessionIdInParam
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\UrlCoder $urlCoder,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        $canUseSessionIdInParam = true
    ) {
        $this->_canUseSessionIdInParam = $canUseSessionIdInParam;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_urlCoder = $urlCoder;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    protected function _getUrl()
    {
        $refererUrl = $this->_request->getServer('HTTP_REFERER');
        $url = (string)$this->_request->getParam(self::PARAM_NAME_REFERER_URL);
        if ($url) {
            $refererUrl = $url;
        }
        $url = $this->_request->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_BASE64_URL);
        if ($url) {
            $refererUrl = $this->_urlCoder->decode($url);
        }
        $url = $this->_request->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        if ($url) {
            $refererUrl = $this->_urlCoder->decode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = $this->_storeManager->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    public function getRefererUrl()
    {
        return $this->_getUrl();
    }

    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  \Magento\Framework\App\ActionInterface
     */
    public function getRedirectUrl($defaultUrl = null)
    {
        $refererUrl = $this->_getUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? $this->_storeManager->getStore()->getBaseUrl() : $defaultUrl;
        }
        return $refererUrl;
    }

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  string
     */
    public function error($defaultUrl)
    {
        $errorUrl = $this->_request->getParam(self::PARAM_NAME_ERROR_URL);
        if (empty($errorUrl)) {
            $errorUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($errorUrl)) {
            $errorUrl = $this->_storeManager->getStore()->getBaseUrl();
        }
        return $errorUrl;
    }

    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return string
     */
    public function success($defaultUrl)
    {
        $successUrl = $this->_request->getParam(self::PARAM_NAME_SUCCESS_URL);
        if (empty($successUrl)) {
            $successUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($successUrl)) {
            $successUrl = $this->_storeManager->getStore()->getBaseUrl();
        }
        return $successUrl;
    }

    /**
     * Set redirect into response
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     */
    public function redirect(\Magento\Framework\App\ResponseInterface $response, $path, $arguments = array())
    {
        if ($this->_session->getCookieShouldBeReceived() &&
            $this->_urlBuilder->getUseSession() &&
            $this->_canUseSessionIdInParam
        ) {
            $arguments += array(
                '_query' => array(
                    $this->_sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId()
                )
            );
        }
        $response->setRedirect($this->_urlBuilder->getUrl($path, $arguments));
    }

    /**
     * Check whether URL is internal
     *
     * @param string $url
     * @return bool
     */
    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            $unsecure = strpos(
                $url,
                $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK,
                    false
                )
            ) === 0;
            $secure = strpos(
                $url,
                $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK,
                    true
                )
            ) === 0;
            return $unsecure || $secure;
        }
        return false;
    }
}
