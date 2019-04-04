<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Validate the "Currency" header entry
 */
class CurrencyValidator implements HttpRequestValidatorInterface
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
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
    }

    /**
     * Validate the header 'Content-Currency' value.
     *
     * @param HttpRequestInterface $request
     * @return void
     */
    public function validate(HttpRequestInterface $request): void
    {
        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $this->storeManager->getStore();
        $headerValue = $request->getHeader('Content-Currency');
        if (!empty($headerValue)) {
            $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
            if (!in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes())) {
                throw new GraphQlInputException(
                    new \Magento\Framework\Phrase('Currency not allowed for store %1', [$currentStore->getStoreId()])
                );
            }
        }
    }
}
