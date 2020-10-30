<?php
/**
 * Response redirector
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Response;

use Laminas\Uri\Uri;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Redirect computes redirect urls responses.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect implements RedirectInterface
{
    private const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';
    private const XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlCoder
     */
    protected $_urlCoder;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var bool
     */
    protected $_canUseSessionIdInParam;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param UrlCoder $urlCoder
     * @param SessionManagerInterface $session
     * @param SidResolverInterface $sidResolver
     * @param UrlInterface $urlBuilder
     * @param Uri|null $uri
     * @param bool $canUseSessionIdInParam
     * @param State|null $appState
     * @param ScopeConfigInterface|null $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        UrlCoder $urlCoder,
        SessionManagerInterface $session,
        SidResolverInterface $sidResolver,
        UrlInterface $urlBuilder,
        Uri $uri = null,
        $canUseSessionIdInParam = true,
        ?State $appState = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->_canUseSessionIdInParam = $canUseSessionIdInParam;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_urlCoder = $urlCoder;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_urlBuilder = $urlBuilder;
        $this->uri = $uri ?: ObjectManager::getInstance()->get(Uri::class);
        $this->appState = $appState ?: ObjectManager::getInstance()->get(State::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Get the referrer url.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getUrl()
    {
        $refererUrl = $this->_request->getServer('HTTP_REFERER');
        $encodedUrl = $this->_request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED)
            ?: $this->_request->getParam(ActionInterface::PARAM_NAME_BASE64_URL);

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
     * @return  ActionInterface
     *
     * @throws  NoSuchEntityException
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
     * @throws  NoSuchEntityException
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
     * @throws NoSuchEntityException
     */
    public function success($defaultUrl)
    {
        $successUrl = $this->_request->getParam(self::PARAM_NAME_SUCCESS_URL);
        $successUrl = $successUrl ?: $defaultUrl;

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
     * @param ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     */
    public function redirect(ResponseInterface $response, $path, $arguments = [])
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
        return strpos($url, 'http') !== false
            ? $this->isInternalUrl($url) || $this->isCustomAdminUrl($url)
            : false;
    }

    /**
     * Is `Use Custom Admin URL` config enabled
     *
     * @return bool
     */
    private function isUseCustomAdminUrlEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_CUSTOM_ADMIN_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns custom admin url
     *
     * @return string
     */
    private function getCustomAdminUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_ADMIN_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is internal custom admin url
     *
     * @param string $url
     * @return bool
     */
    private function isCustomAdminUrl(string $url): bool
    {
        if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML && $this->isUseCustomAdminUrlEnabled()) {
            return strpos($url, $this->getCustomAdminUrl()) === 0;
        }

        return false;
    }

    /**
     * Is url internal
     *
     * @param string $url
     * @return bool
     */
    private function isInternalUrl(string $url): bool
    {
        $directLinkType = UrlInterface::URL_TYPE_DIRECT_LINK;
        $unsecureBaseUrl = $this->_storeManager->getStore()
            ->getBaseUrl($directLinkType, false);
        $secureBaseUrl = $this->_storeManager->getStore()
            ->getBaseUrl($directLinkType, true);

        return strpos($url, (string) $unsecureBaseUrl) === 0 || strpos($url, (string) $secureBaseUrl) === 0;
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
            && !empty($refererQuery[StoreManagerInterface::PARAM_NAME])
            && ($refererQuery[StoreManagerInterface::PARAM_NAME] !== $store->getCode())
        ) {
            $refererQuery[StoreManagerInterface::PARAM_NAME] = $store->getCode();
        }

        return $refererQuery;
    }
}
