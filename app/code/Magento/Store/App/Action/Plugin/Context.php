<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\Action\Plugin;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;

/**
 * Class ContextPlugin
 */
class Context
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
            StoreResolverInterface::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        if (is_array($storeCode)) {
            if (!isset($storeCode['_data']['code'])) {
                $this->processInvalidStoreRequested();
            }
            $storeCode = $storeCode['_data']['code'];
        }
        if ($storeCode === '') {
            //Empty code - is an invalid code and it was given explicitly
            //(the value would be null if the code wasn't found).
            $this->processInvalidStoreRequested();
        }
        try {
            $currentStore = $this->storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $exception) {
            $this->processInvalidStoreRequested($exception);
        }

        $this->updateContext($currentStore);
    }

    /**
     * Take action in case of invalid store requested.
     *
     * @param \Throwable|null  $previousException
     * @return void
     * @throws NotFoundException
     */
    private function processInvalidStoreRequested(
        \Throwable $previousException = null
    ) {
        $store = $this->storeManager->getStore();
        $this->updateContext($store);

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
     * @param StoreInterface $store
     * @return void
     */
    private function updateContext(StoreInterface $store)
    {
        $this->httpContext->setValue(
            StoreManagerInterface::CONTEXT_STORE,
            $store->getCode(),
            $this->storeManager->getDefaultStoreView()->getCode()
        );

        /** @var StoreInterface $defaultStore */
        $defaultStore = $this->storeManager->getWebsite()->getDefaultStore();
        $this->httpContext->setValue(
            HttpContext::CONTEXT_CURRENCY,
            $this->session->getCurrencyCode()
                ?: $store->getDefaultCurrencyCode(),
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
