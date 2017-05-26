<?php
/**
 * Response redirector
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Response;

use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Api\Data\StoreInterface;

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
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param bool $canUseSessionIdInParam
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        $url = $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_BASE64_URL);
        if ($url) {
            $refererUrl = $this->_urlCoder->decode($url);
        }
        $url = $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED);
        if ($url) {
            $refererUrl = $this->_urlCoder->decode($url);
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
     * {@inheritdoc}
     *
     * @param array $arguments
     * @return array
     */
    public function updatePathParams(array $arguments)
    {
        if ($this->_session->getCookieShouldBeReceived()
            && $this->_sidResolver->getUseSessionInUrl()
            && $this->_canUseSessionIdInParam
        ) {
            $arguments += [
                '_query' => [
                    $this->_sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId(),
                ]
            ];
        }
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
            $currentStore = $this->_storeManager->getStore();
            $currentStoreUnsecureBaseUrl = $currentStore
                ->getBaseUrl($directLinkType, false);
            $currentStoreSecureBaseUrl = $currentStore
                ->getBaseUrl($directLinkType, true);
            $internal = (
                strpos($url, $currentStoreUnsecureBaseUrl) === 0
                || strpos($url, $currentStoreSecureBaseUrl) === 0
            );
            if (true === $internal) {
                $internal = ! $this->_isUrlFromOtherStore($url, $currentStore);
            }
            return $internal;
        }
        return false;
    }

    /**
     * check if an 'internal' url is coming from another store
     *
     * @param string $url
     * @param \Magento\Store\Api\Data\StoreInterface $currentStore
     * @return bool
     */
    protected function _isUrlFromOtherStore($url, \Magento\Store\Api\Data\StoreInterface $currentStore)
    {
        /**
         * check if the referer belongs to another storeview
         */
        $linkFromOtherStoreview = false;
        $stores = $this->_storeManager->getStores(true);
        foreach ($stores as $store) {
            if ($currentStore->getId() !== $store->getId()) {
                $isChildStore = $this->_checkStoreIsChildOfCurrentStore(
                    $url,
                    $currentStore,
                    $store
                );
                if (true === $isChildStore
                    && false === $linkFromOtherStoreview) {
                    $linkFromOtherStoreview = true;
                }
            }
        }
        return $linkFromOtherStoreview;
    }

    /**
     * check if a store has a 'child' url of the current store
     *
     * example:
     * - $currentStore has url https://store.com/
     * - $store has url https://store.com/nl/
     * Here we should return true since the $store's url is just adding an
     * extra path to the $currentStore.
     *
     * @param string $url
     * @param \Magento\Store\Api\Data\StoreInterface $currentStore
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return bool
     */
    protected function _checkStoreIsChildOfCurrentStore(
        $url,
        \Magento\Store\Api\Data\StoreInterface $currentStore,
        \Magento\Store\Api\Data\StoreInterface $store
    ) {
        $directLinkType = \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK;

        $currentStoreUnsecureBaseUrl = $currentStore
            ->getBaseUrl($directLinkType, false);
        $currentStoreSecureBaseUrl = $currentStore
            ->getBaseUrl($directLinkType, true);

        $unsecureBaseUrl = $store
            ->getBaseUrl($directLinkType, false);
        $secureBaseUrl = $store
            ->getBaseUrl($directLinkType, true);
        // does the beginning of the url matches another
        // store in this system
        // indicating the other store is just an additional
        // item in the url path
        //
        // example:
        // basestore = http://example.com/
        // german store = http://example.com/de/
        $canMatchOtherStoreView = (
            strpos($url, $unsecureBaseUrl) === 0
            || strpos($url, $secureBaseUrl) === 0
        );
        // check if the other store's baseurl could be the
        // 'parent' of the current stores baseurl unless the
        // baseurl is exactly the same.
        $otherStoreViewSubCurrentStore = (
            (strpos($unsecureBaseUrl, $currentStoreUnsecureBaseUrl) === 0
            || strpos($secureBaseUrl, $currentStoreSecureBaseUrl) === 0)
            && ($currentStoreUnsecureBaseUrl !== $unsecureBaseUrl
            && $currentStoreSecureBaseUrl !== $secureBaseUrl)
        );
        // when the url can also match the other store
        // and the other store's baseurl is a 'child' of the
        // current store's baseurl state the link is coming
        // from external (other store)
        return $canMatchOtherStoreView && $otherStoreViewSubCurrentStore;
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

        $redirectParsedUrl = parse_url($refererUrl);
        $refererQuery = [];

        if (!isset($redirectParsedUrl['query'])) {
            return $refererUrl;
        }

        parse_str($redirectParsedUrl['query'], $refererQuery);

        $refererQuery = $this->normalizeRefererQueryParts($refererQuery);
        $normalizedUrl = $redirectParsedUrl['scheme']
            . '://'
            . $redirectParsedUrl['host']
            . (isset($redirectParsedUrl['port']) ? ':' . $redirectParsedUrl['port'] : '')
            . $redirectParsedUrl['path']
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
            && !empty($refererQuery[StoreResolverInterface::PARAM_NAME])
            && ($refererQuery[StoreResolverInterface::PARAM_NAME] !== $store->getCode())
        ) {
            $refererQuery[StoreResolverInterface::PARAM_NAME] = $store->getCode();
        }

        return $refererQuery;
    }
}
