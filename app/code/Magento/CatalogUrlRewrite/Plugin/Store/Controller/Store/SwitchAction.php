<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Store\Controller\Store;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SidResolverInterface;

/**
 * Plugin makes connection between Store and UrlRewrite modules
 * because Magento\Store\Controller\Store\SwitchAction should not know about UrlRewrite module functionality
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchAction
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    private $requestFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    private $sidResolver;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param HttpRequest $request
     * @param StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory
     * @param RedirectFactory $redirectFactory
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        HttpRequest $request,
        StoreRepositoryInterface $storeRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory,
        RedirectFactory $redirectFactory,
        UrlHelper $urlHelper = null,
        \Magento\Framework\Session\Generic $session = null,
        \Magento\Framework\Session\SidResolverInterface $sidResolver = null
    ) {
        $this->urlFinder = $urlFinder;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->redirect = $redirect;
        $this->requestFactory = $requestFactory;
        $this->redirectFactory = $redirectFactory;
        $this->urlHelper = $urlHelper ?: ObjectManager::getInstance()->get(UrlHelper::class);
        $this->session = $session ?: ObjectManager::getInstance()->get(Generic::class);
        $this->sidResolver = $sidResolver ?: ObjectManager::getInstance()->get(SidResolverInterface::class);
    }

    /**
     * @param \Magento\Store\Controller\Store\SwitchAction $subject
     * @param \Magento\Framework\Controller\ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\Store\Controller\Store\SwitchAction $subject,
        \Magento\Framework\Controller\ResultInterface $result
    ): ResultInterface {
        try {
            $url = $this->redirect->getRedirectUrl();
            /** @var Store $store */
            $store = $this->storeRepository->getActiveStoreByCode(
                $this->request->getParam(StoreResolverInterface::PARAM_NAME)
            );
        } catch (LocalizedException $e) {
            return $result;
        }

        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $url]);

        // works only for catalog urls
        $query = [];
        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $query);
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        if ($fromStore = $query['___from_store']) {
            // remove SID, ___from_store, ___store from target url
            $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
            $url = $this->urlHelper->removeRequestParam($url, $sidName);
            $url = $this->urlHelper->removeRequestParam($url, '___from_store');
            $url = $this->urlHelper->removeRequestParam($url, StoreResolverInterface::PARAM_NAME);

            $urlPath = ltrim($request->getPathInfo(), '/');
            try {
                $oldStoreId = $this->storeRepository->get($fromStore)->getId();
                $oldRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::REQUEST_PATH => $urlPath,
                    UrlRewrite::STORE_ID => $oldStoreId,
                    UrlRewrite::ENTITY_TYPE => [
                        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE,
                    ]
                ]);
            } catch (NoSuchEntityException $exception) {
                $oldRewrite = null;
            }
            if ($oldRewrite) {
                // we're in catalog and can check whether we have target URL or a user should be redirected to base url
                $currentRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::REQUEST_PATH => $urlPath,
                    UrlRewrite::STORE_ID => $store->getId(),
                ]);
                if (null === $currentRewrite) {
                    return $resultRedirect->setUrl($store->getBaseUrl());
                }
            }
        }

        return $resultRedirect->setUrl($url);
    }
}
