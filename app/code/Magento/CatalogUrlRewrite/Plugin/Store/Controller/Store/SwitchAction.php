<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Store\Controller\Store;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;

/**
 * Plugin makes connection between Store and UrlRewrite modules
 * because Magento\Store\Controller\Store\SwitchAction should not know about UrlRewrite module functionality
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
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    private $requestFactory;

    /**
     * @var ResponseInterface|HttpResponse
     */
    private $response;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param HttpRequest $request
     * @param StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        HttpRequest $request,
        StoreRepositoryInterface $storeRepository,
        \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory,
        ResponseInterface $response = null
    ) {
        $this->urlFinder = $urlFinder;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->requestFactory = $requestFactory;
        $this->response = $response ?: ObjectManager::getInstance()->get(ResponseInterface::class);
    }

    /**
     * @param \Magento\Store\Controller\Store\SwitchAction $subject
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(\Magento\Store\Controller\Store\SwitchAction $subject) {
        try {
            $location = $this->response->getHeader('Location');
            $url = $location ? $location->getUri() : null;
            /** @var Store $store */
            $store = $this->storeRepository->getActiveStoreByCode(
                $this->request->getParam(StoreResolverInterface::PARAM_NAME)
            );
        } catch (LocalizedException $e) {
            return;
        }

        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $url]);

        if ($fromStore = $this->request->getParam('___from_store')) {
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
                    /** @var \Magento\Framework\App\Response\Http $response */
                    return $this->response->setRedirect($store->getBaseUrl());
                }
            }
        }
        return $this->response->setRedirect($url);
    }
}
