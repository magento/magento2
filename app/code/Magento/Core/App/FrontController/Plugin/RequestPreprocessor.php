<?php
/**
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
namespace Magento\Core\App\FrontController\Plugin;

class RequestPreprocessor
{
    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\State $appState
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\State $appState,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\App\ResponseFactory $responseFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_appState = $appState;
        $this->_url = $url;
        $this->_storeConfig = $storeConfig;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Auto-redirect to base url (without SID) if the requested url doesn't match it.
     * By default this feature is enabled in configuration.
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        $request = $arguments[0];
        if ($this->_appState->isInstalled() && !$request->isPost() && $this->_isBaseUrlCheckEnabled()) {
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\UrlInterface::URL_TYPE_WEB,
                $this->_storeManager->getStore()->isCurrentlySecure()
            );
            if ($baseUrl) {
                $uri = parse_url($baseUrl);
                if (!$this->_isBaseUrlCorrect($uri, $request)) {
                    $redirectUrl = $this->_url->getRedirectUrl(
                        $this->_url->getUrl(ltrim($request->getPathInfo(), '/'), array('_nosid' => true))
                    );
                    $redirectCode = (int)$this->_storeConfig->getConfig('web/url/redirect_to_base') !== 301
                        ? 302
                        : 301;

                    $response = $this->_responseFactory->create();
                    $response->setRedirect($redirectUrl, $redirectCode);
                    return $response;
                }
            }
        }
        $request->setDispatched(false);

        return $invocationChain->proceed($arguments);
    }

    /**
     * Is base url check enabled
     *
     * @return bool
     */
    protected function _isBaseUrlCheckEnabled()
    {
        return (bool) $this->_storeConfig->getConfig('web/url/redirect_to_base');
    }

    /**
     * Check if base url enabled
     *
     * @param array $uri
     * @param \Magento\App\Request\Http $request
     * @return bool
     */
    protected function _isBaseUrlCorrect($uri, $request)
    {
        $requestUri = $request->getRequestUri() ? $request->getRequestUri() : '/';
        return (!isset($uri['scheme']) || $uri['scheme'] === $request->getScheme())
            && (!isset($uri['host']) || $uri['host'] === $request->getHttpHost())
            && (!isset($uri['path']) || strpos($requestUri, $uri['path']) !== false);
    }
}
