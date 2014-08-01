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
namespace Magento\UrlRedirect\Controller;

/**
 * UrlRedirect Controller Router
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /** @var \Magento\Framework\UrlInterface */
    protected $url;

    /** @var \Magento\Framework\App\State */
    protected $appState;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $response;

    /** @var \Magento\UrlRedirect\Service\V1\UrlMatcherInterface */
    protected $urlMatcher;

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\UrlRedirect\Service\V1\UrlMatcherInterface $urlMatcher
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\UrlRedirect\Service\V1\UrlMatcherInterface $urlMatcher
    ) {
        $this->actionFactory = $actionFactory;
        $this->url = $url;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->appState->isInstalled()) {
            $this->response->setRedirect($this->url->getUrl('install'))->sendResponse();
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $urlRewrite = $this->urlMatcher->match($identifier, $this->storeManager->getStore()->getId());
        if ($urlRewrite === null) {
            return null;
        }

        $redirectType = $urlRewrite->getRedirectType();
        if ($redirectType) {
            $redirectCode = $redirectType == \Magento\UrlRedirect\Model\OptionProvider::PERMANENT ? 301 : 302;
            $this->response->setRedirect($urlRewrite->getTargetPath(), $redirectCode);
            $request->setDispatched(true);
            return $this->actionFactory->createController(
                'Magento\Framework\App\Action\Redirect',
                array('request' => $request)
            );
        }

        $request->setPathInfo('/' . $urlRewrite->getTargetPath());
        return $this->actionFactory->createController(
            'Magento\Framework\App\Action\Forward',
            array('request' => $request)
        );
    }
}
