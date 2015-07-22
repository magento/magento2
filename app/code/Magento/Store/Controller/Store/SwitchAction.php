<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreResolver;

class SwitchAction extends Action
{
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    protected $cookie;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param ActionContext $context
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param HttpContext $httpContext
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ActionContext $context,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        HttpContext $httpContext,
        StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($context);
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookie = $cookieManager;
        $this->httpContext = $httpContext;
        $this->storeRepository = $storeRepository;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * @return void
     */
    public function execute()
    {
        $storeCode = $this->_request->getParam(
            StoreResolver::PARAM_NAME,
            $this->cookie->getCookie(StoreResolver::COOKIE_NAME)
        );

        try {
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        } catch (\InvalidArgumentException $e) { // TODO: MAGETWO-39826 Need to replace on NoSuchEntityException
            $error = __('Requested store is not found');
        }

        if (isset($error)) {
            $this->messageManager->addError($error);
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
            return;
        }

        if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath($store->getStorePath());
            $this->cookie->deleteCookie(StoreResolver::COOKIE_NAME, $cookieMetadata);
        } else {
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setHttpOnly(true)
                ->setDurationOneYear()
                ->setPath($store->getStorePath());
            $this->cookie->setPublicCookie(StoreResolver::COOKIE_NAME, $store->getCode(), $cookieMetadata);
            $this->httpContext->setValue(Store::ENTITY, $store->getCode(), Store::DEFAULT_CODE);
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
