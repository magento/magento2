<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Store\Controller\Store;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Api\StoreRepositoryInterface;

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
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    private $requestFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param HttpRequest $request
     * @param StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        HttpRequest $request,
        StoreRepositoryInterface $storeRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory
    )
    {
        $this->urlFinder = $urlFinder;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->redirect = $redirect;
        $this->requestFactory = $requestFactory;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
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
    ): ResultInterface
    {
        try {
            $url = $this->redirect->getRedirectUrl();
            $store = $this->storeRepository->getActiveStoreByCode(
                $this->request->getParam(StoreResolverInterface::PARAM_NAME)
            );
        } catch (LocalizedException $e) {
            return $result;
        }

        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $url]);

        // works only for catalog urls
        if ($fromStore = $request->getParam('___from_store')) {
            $urlPath = ltrim($request->getPathInfo(), '/');
            try {
                $oldStoreId = $this->storeManager->getStore($fromStore)->getId();
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
                    /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->redirectFactory->create();
                    $result = $resultRedirect->setPath('', [
                        '_scope' => $store->getCode(),
                        '_query' => [
                            '___from_store' => $fromStore,
                            '___store' => $request->getParam('___store'),
                        ]]);
                }
            }
        }

        return $result;
    }
}
