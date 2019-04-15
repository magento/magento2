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
            /** @var \Magento\Store\Model\Store $defaultStore */
            $defaultStore = $this->storeManager->getWebsite()->getDefaultStore();
            /** @var \Magento\Store\Model\Store $currentStore */
            $currentStore = $this->storeManager->getStore();

            if (!empty($headerValue)) {
                $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
                if (in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes())) {
                    $currentStore->setCurrentCurrencyCode($headerCurrency);
                }
            } else {
                if ($this->session->getCurrencyCode()) {
                    $currentStore->setCurrentCurrencyCode($this->session->getCurrencyCode());
                } else {
                    $this->httpContext->setValue(
                        HttpContext::CONTEXT_CURRENCY,
                        $defaultStore->getCurrentCurrencyCode(),
                        $defaultStore->getDefaultCurrencyCode()
                    );
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //skip store not found exception as it will be handled in graphql validation
            $this->logger->warning($e->getMessage());
        }
    }
}
