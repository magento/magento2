<?php
/**
 * Response redirector
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Response;

use Magento\Framework\App\ObjectManager;

/**
 * Class Redirect computes redirect urls responses.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Redirect implements \Magento\Framework\App\Response\RedirectInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @var \Laminas\Uri\Uri|null
     */
    private $uri;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Laminas\Uri\Uri|null $uri
     * @param bool $canUseSessionIdInParam
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\UrlCoder $urlCoder,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Laminas\Uri\Uri $uri = null,
        $canUseSessionIdInParam = true
    ) {
        $this->_canUseSessionIdInParam = $canUseSessionIdInParam;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_urlCoder = $urlCoder;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_urlBuilder = $urlBuilder;
        $this->uri = $uri ?: ObjectManager::getInstance()->get(\Laminas\Uri\Uri::class);
    }

    /**
     * Get the referrer url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getUrl()
    {
        $refererUrl = $this->_request->getServer('HTTP_REFERER');
        $encodedUrl = $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED)
            ?: $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_BASE64_URL);

        if ($encodedUrl) {
            $refererUrl = $this->_urlCoder->decode($encodedUrl);
        } else {
            $url = (string)$this->_request->getParam(self::PARAM_NAME_REFERER_URL);
            if ($url) {
                $refererUrl = $url;
            }
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = $this->_storeManager->getStore()->getBaseUrl();
        } else {
            $refererUrl = $this->normalizeRefererUrl($refererUrl);
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
     *
     * @throws  \Magento\Framework\Exception\NoSuchEntityException
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
     * @param   string $defaultUrl
     * @return  string
     *
     * @throws  \Magento\Framework\Exception\NoSuchEntityException
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
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @inheritdoc
     */
    public function updatePathParams(array $arguments)
    {
        return $arguments;
    }

    /**
     * Set redirect into response
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     */
    public function redirect(\Magento\Framework\App\ResponseInterface $response, $path, $arguments = [])
    {
        $arguments = $this->updatePathParams($arguments);
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
            $directLinkType = \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK;
            $unsecureBaseUrl = $this->_storeManager->getStore()->getBaseUrl($directLinkType, false);
            $secureBaseUrl = $this->_storeManager->getStore()->getBaseUrl($directLinkType, true);
            return (strpos($url, (string) $unsecureBaseUrl) === 0) || (strpos($url, (string) $secureBaseUrl) === 0);
        }
        return false;
    }

    /**
     * Normalize path to avoid wrong store change
     *
     * @param string $refererUrl
     * @return string
     */
    protected function normalizeRefererUrl($refererUrl)
    {
        if (!$refererUrl || !filter_var($refererUrl, FILTER_VALIDATE_URL)) {
            return $refererUrl;
        }

        $redirectParsedUrl = $this->uri->parse($refererUrl);

        if (!$redirectParsedUrl->getQuery()) {
            return $refererUrl;
        }

        $refererQuery = $redirectParsedUrl->getQueryAsArray();

        $refererQuery = $this->normalizeRefererQueryParts($refererQuery);
        $normalizedUrl = $redirectParsedUrl->getScheme()
            . '://'
            . $redirectParsedUrl->getHost()
            . ($redirectParsedUrl->getPort() ? ':' . $redirectParsedUrl->getPort() : '')
            . $redirectParsedUrl->getPath()
            . ($refererQuery ? '?' . http_build_query($refererQuery) : '');

        return $normalizedUrl;
    }

    /**
     * Normalize special parts of referer query
     *
     * @param array $refererQuery
     * @return array
     */
    protected function normalizeRefererQueryParts($refererQuery)
    {
        $store = $this->_storeManager->getStore();

        if ($store
            && !empty($refererQuery[\Magento\Store\Model\StoreManagerInterface::PARAM_NAME])
            && ($refererQuery[\Magento\Store\Model\StoreManagerInterface::PARAM_NAME] !== $store->getCode())
        ) {
            $refererQuery[\Magento\Store\Model\StoreManagerInterface::PARAM_NAME] = $store->getCode();
        }

        return $refererQuery;
    }
}
