<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionInterface;

/**
 * UrlRewrite Controller Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface|HttpResponse
     */
    protected $response;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        UrlFinderInterface $urlFinder
    ) {
        $this->actionFactory = $actionFactory;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Match corresponding URL Rewrite and modify request
     *
     * @param \Magento\Framework\App\RequestInterface|HttpRequest $request
     * @return ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        //If we're in the process of switching stores then matching rewrite
        //rule from previous store because the URL was not changed yet from
        //old store's format.
        if ($fromStore = $request->getParam('___from_store')) {
            $oldStoreId = $this->storeManager->getStore($fromStore)->getId();
            $oldRewrite = $this->getRewrite(
                $request->getPathInfo(),
                $oldStoreId
            );
            if ($oldRewrite && $oldRewrite->getRedirectType() === 0) {
                //If there is a match and it's a correct URL then just
                //redirecting to current store's URL equivalent,
                //otherwise just continuing finding a rule within current store.
                $currentRewrite = $this->urlFinder->findOneByData(
                    [
                        UrlRewrite::ENTITY_TYPE => $oldRewrite->getEntityType(),
                        UrlRewrite::ENTITY_ID => $oldRewrite->getEntityId(),
                        UrlRewrite::STORE_ID =>
                            $this->storeManager->getStore()->getId(),
                        UrlRewrite::REDIRECT_TYPE => 0,
                    ]
                );
                if ($currentRewrite
                    && $currentRewrite->getRequestPath()
                    !== $oldRewrite->getRequestPath()
                ) {
                    return $this->redirect(
                        $request,
                        $this->url->getUrl(
                            '',
                            ['_direct' => $currentRewrite->getRequestPath()]
                        ),
                        OptionProvider::TEMPORARY
                    );
                }
            }
        }

        $rewrite = $this->getRewrite(
            $request->getPathInfo(),
            $this->storeManager->getStore()->getId()
        );
        if ($rewrite === null) {
            //No rewrite rule matching current URl found, continuing with
            //processing of this URL.
            return null;
        }
        if ($rewrite->getRedirectType()) {
            //Rule requires the request to be redirected to another URL
            //and cannot be processed further.
            return $this->processRedirect($request, $rewrite);
        }
        //Rule provides actual URL that can be processed by a controller.
        $request->setAlias(
            UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
            $rewrite->getRequestPath()
        );
        $request->setPathInfo('/' . $rewrite->getTargetPath());
        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param UrlRewrite $rewrite
     * @return ActionInterface|null
     */
    protected function processRedirect($request, $rewrite)
    {
        $target = $rewrite->getTargetPath();
        if ($rewrite->getEntityType() !== Rewrite::ENTITY_TYPE_CUSTOM
            || ($prefix = substr($target, 0, 6)) !== 'http:/' && $prefix !== 'https:'
        ) {
            $target = $this->url->getUrl('', ['_direct' => $target]);
        }
        return $this->redirect($request, $target, $rewrite->getRedirectType());
    }

    /**
     * @param \Magento\Framework\App\RequestInterface|HttpRequest $request
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
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewrite|null
     */
    protected function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'),
            UrlRewrite::STORE_ID => $storeId,
        ]);
    }
}
