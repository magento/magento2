<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * UrlRewrite Controller Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @param ActionFactory $actionFactory
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        ActionFactory $actionFactory,
        UrlInterface $url,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        UrlFinderInterface $urlFinder
    ) {
        $this->actionFactory = $actionFactory;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Match corresponding URL Rewrite and modify request.
     *
     * @param RequestInterface|HttpRequest $request
     * @return ActionInterface|null
     * @throws NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        $rewrite = $this->getRewrite(
            $request->getPathInfo(),
            $this->storeManager->getStore()->getId()
        );

        if ($rewrite === null) {
            // No rewrite rule matching current URl found, continuing with
            // processing of this URL.
            return null;
        }

        $requestStringTrimmed = ltrim($request->getRequestString(), '/');
        $rewriteRequestPath = $rewrite->getRequestPath();
        $rewriteTargetPath = $rewrite->getTargetPath();
        $rewriteTargetPathTrimmed = ltrim($rewriteTargetPath, '/');

        if (preg_replace('/\?.*/', '', $rewriteRequestPath) === preg_replace('/\?.*/', '', $rewriteTargetPath) &&
            (
                !$requestStringTrimmed ||
                !$rewriteTargetPathTrimmed ||
                strpos($requestStringTrimmed, $rewriteTargetPathTrimmed) === 0
            )
        ) {
            // Request and target paths of rewrite found without query params are equal and current request string
            // starts with request target path, continuing with processing of this URL.
            return null;
        }

        if ($rewrite->getRedirectType()) {
            // Rule requires the request to be redirected to another URL
            // and cannot be processed further.
            return $this->processRedirect($request, $rewrite);
        }
        // Rule provides actual URL that can be processed by a controller.
        $request->setAlias(
            UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
            $rewriteRequestPath
        );
        $request->setPathInfo('/' . $rewriteTargetPath);
        return $this->actionFactory->create(
            Forward::class
        );
    }

    /**
     * Process redirect
     *
     * @param RequestInterface $request
     * @param UrlRewrite $rewrite
     *
     * @return ActionInterface|null
     */
    protected function processRedirect($request, $rewrite)
    {
        $target = $rewrite->getTargetPath();
        if ($rewrite->getEntityType() !== Rewrite::ENTITY_TYPE_CUSTOM
            || ($prefix = substr($target, 0, 6)) !== 'http:/' && $prefix !== 'https:'
        ) {
            $target = $this->url->getUrl('', ['_direct' => $target, '_query' => $request->getParams()]);
        }
        return $this->redirect($request, $target, $rewrite->getRedirectType());
    }

    /**
     * Redirect to target URL
     *
     * @param RequestInterface|HttpRequest $request
     * @param string $url
     * @param int $code
     * @return ActionInterface
     */
    protected function redirect($request, $url, $code)
    {
        $this->response->setRedirect($url, $code);
        $request->setDispatched(true);

        return $this->actionFactory->create(Redirect::class);
    }

    /**
     * Find rewrite based on request data
     *
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewrite|null
     */
    protected function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'),
                UrlRewrite::STORE_ID => $storeId,
            ]
        );
    }
}
