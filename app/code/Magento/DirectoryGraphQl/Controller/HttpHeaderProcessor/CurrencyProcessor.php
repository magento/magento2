<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

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
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->session = $session;
    }

    /**
     * Handle the header 'Content-Currency' value.
     *
     * @inheritDoc
     * @throws GraphQlInputException
     */
    public function processHeaderValue(string $headerValue, HttpRequestInterface $request) : void
    {
        /** @var \Magento\Store\Model\Store $defaultStore */
        $defaultStore = $this->storeManager->getWebsite()->getDefaultStore();
        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $this->storeManager->getStore();

        if (!empty($headerValue)) {
            $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
            if (in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes())) {
                $currentStore->setCurrentCurrencyCode($headerCurrency);
            } else {
                throw new GraphQlInputException(
                    new \Magento\Framework\Phrase('Currency not allowed for store %1', [$currentStore->getStoreId()])
                );
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
    }
}
