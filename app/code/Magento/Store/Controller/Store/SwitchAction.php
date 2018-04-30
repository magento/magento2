<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Switch current store view.
 */
class SwitchAction extends Action
{
    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

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
     * Initialize dependencies.
     *
     * @param ActionContext $context
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     */
    public function __construct(
        ActionContext $context,
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface $storeManager,
        UrlHelper $urlHelper = null,
        \Magento\Framework\Session\Generic $session = null,
        \Magento\Framework\Session\SidResolverInterface $sidResolver = null
    ) {
        parent::__construct($context);
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper ?: ObjectManager::getInstance()->get(UrlHelper::class);
        $this->session = $session ?: ObjectManager::getInstance()->get(Generic::class);
        $this->sidResolver = $sidResolver ?: ObjectManager::getInstance()->get(SidResolverInterface::class);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $currentActiveStore = $this->storeManager->getStore();
        $storeCode = $this->_request->getParam(
            StoreResolver::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );

        try {
            /** @var Store $store */
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found');
        }

        $redirectUrl = $this->_redirect->getRedirectUrl();
        $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
        if ($this->sidResolver->getUseSessionInUrl()) {
            $redirectUrl = $this->urlHelper->addRequestParam($redirectUrl, [
                $sidName => $this->session->getSessionId()
            ]);
        }

        if (isset($error)) {
            $this->messageManager->addError($error);
            $this->getResponse()->setRedirect($redirectUrl);
            return;
        }

        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView->getId() == $store->getId()) {
            $this->storeCookieManager->deleteStoreCookie($store);
        } else {
            $this->httpContext->setValue(Store::ENTITY, $store->getCode(), $defaultStoreView->getCode());
            $this->storeCookieManager->setStoreCookie($store);
        }

        if ($store->isUseStoreInUrl()) {
            // Change store code in redirect url
            if (strpos($redirectUrl, $currentActiveStore->getBaseUrl()) !== false) {
                $this->getResponse()->setRedirect(
                    str_replace(
                        $currentActiveStore->getBaseUrl(),
                        $store->getBaseUrl(),
                        $redirectUrl
                    )
                );
            } else {
                $this->getResponse()->setRedirect($store->getBaseUrl());
            }
        } else {
            $this->getResponse()->setRedirect($redirectUrl);
        }
    }
}
