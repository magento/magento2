<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Controller\HttpHeaderProcessor;

use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Process the "Currency" header entry
 */
class CurrencyProcessor implements HttpHeaderProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param SessionManagerInterface $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        SessionManagerInterface $session,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * Handle the header 'Content-Currency' value.
     *
     * @param string $headerValue
     * @return void
     */
    public function processHeaderValue(string $headerValue) : void
    {
        try {
            if (!empty($headerValue)) {
                $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
                /** @var \Magento\Store\Model\Store $currentStore */
                $currentStore = $this->storeManager->getStore();
                if (in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes(true))) {
                    $currentStore->setCurrentCurrencyCode($headerCurrency);
                } else {
                    /** @var \Magento\Store\Model\Store $store */
                    $store = $this->storeManager->getStore() ?? $this->storeManager->getDefaultStoreView();
                    //skip store not found exception as it will be handled in graphql validation
                    $this->logger->warning(__('Currency not allowed for store %1', [$store->getCode()]));
                    $this->httpContext->setValue(
                        HttpContext::CONTEXT_CURRENCY,
                        $headerCurrency,
                        $store->getDefaultCurrency()->getCode()
                    );
                }
            } else {
                if ($this->session->getCurrencyCode()) {
                    /** @var \Magento\Store\Model\Store $currentStore */
                    $currentStore = $this->storeManager->getStore() ?? $this->storeManager->getDefaultStoreView();
                    $currentStore->setCurrentCurrencyCode($this->session->getCurrencyCode());
                } else {
                    /** @var \Magento\Store\Model\Store $store */
                    $store = $this->storeManager->getStore() ?? $this->storeManager->getDefaultStoreView();
                    $this->httpContext->setValue(
                        HttpContext::CONTEXT_CURRENCY,
                        $store->getCurrentCurrency()->getCode(),
                        $store->getDefaultCurrency()->getCode()
                    );
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //skip store not found exception as it will be handled in graphql validation
            $this->logger->warning($e->getMessage());
        }
    }
}
