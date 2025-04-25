<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\Action\Plugin;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ContextPlugin
 */
class Context
{
    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @param SessionManagerInterface $session
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     */
    public function __construct(
        SessionManagerInterface $session,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        StoreCookieManagerInterface $storeCookieManager
    ) {
        $this->session      = $session;
        $this->httpContext  = $httpContext;
        $this->storeManager = $storeManager;
        $this->storeCookieManager = $storeCookieManager;
    }

    /**
     * Set store and currency to http context.
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        AbstractAction $subject,
        RequestInterface $request
    ) {
        if ($this->isAlreadySet()) {
            //If required store related value were already set for
            //HTTP processors then just continuing as we were.
            return;
        }

        /** @var string|array|null $storeCode */
        $storeCode = $request->getParam(
            StoreManagerInterface::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        if (is_array($storeCode)) {
            if (!isset($storeCode['_data']['code'])) {
                $this->processInvalidStoreRequested($request);
            }
            $storeCode = $storeCode['_data']['code'];
        }
        if ($storeCode === '') {
            //Empty code - is an invalid code and it was given explicitly
            //(the value would be null if the code wasn't found).
            $this->processInvalidStoreRequested($request);
        }
        try {
            $currentStore = $this->storeManager->getStore($storeCode);
            $this->updateContext($request, $currentStore);
        } catch (NoSuchEntityException $exception) {
            $this->processInvalidStoreRequested($request, $exception);
        }
    }

    /**
     * Take action in case of invalid store requested.
     *
     * @param RequestInterface $request
     * @param NoSuchEntityException|null $previousException
     * @return void
     * @throws NotFoundException
     */
    private function processInvalidStoreRequested(
        RequestInterface $request,
        ?NoSuchEntityException $previousException = null
    ) {
        $store = $this->storeManager->getStore();
        $this->updateContext($request, $store);

        throw new NotFoundException(
            $previousException
                ? __($previousException->getMessage())
                : __('Invalid store requested.'),
            $previousException
        );
    }

    /**
     * Update context accordingly to the store found.
     *
     * @param RequestInterface $request
     * @param StoreInterface $store
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateContext(RequestInterface $request, StoreInterface $store)
    {
        switch (true) {
            case $store->isUseStoreInUrl():
                $defaultStore = $store;
                break;
            case ScopeInterface::SCOPE_STORE == $request->getServerValue(StoreManager::PARAM_RUN_TYPE):
                $defaultStoreCode = $request->getServerValue(StoreManager::PARAM_RUN_CODE);
                $defaultStore = $this->storeManager->getStore($defaultStoreCode);
                break;
            default:
                $defaultStoreCode = $this->storeManager->getDefaultStoreView()->getCode();
                $defaultStore = $this->storeManager->getStore($defaultStoreCode);
                break;
        }
        $this->httpContext->setValue(
            StoreManagerInterface::CONTEXT_STORE,
            $store->getCode(),
            $defaultStore->getCode()
        );
        $this->httpContext->setValue(
            HttpContext::CONTEXT_CURRENCY,
            $this->session->getCurrencyCode() ?: $store->getDefaultCurrencyCode(),
            $defaultStore->getDefaultCurrencyCode()
        );
    }

    /**
     * Check if there is a need to find the current store.
     *
     * @return bool
     */
    private function isAlreadySet(): bool
    {
        $storeKey = StoreManagerInterface::CONTEXT_STORE;

        return $this->httpContext->getValue($storeKey) !== null;
    }
}
