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
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Validate the header 'Content-Currency' value.
     *
     * @param HttpRequestInterface $request
     * @return void
     */
    public function validate(HttpRequestInterface $request): void
    {
        try {
            $headerValue = $request->getHeader('Content-Currency');
            if (!empty($headerValue)) {
                $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
                /** @var \Magento\Store\Model\Store $currentStore */
                $currentStore = $this->storeManager->getStore();
                if (!in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes())) {
                    throw new GraphQlInputException(
                        __('Currency not allowed for store %1', [$currentStore->getCode()])
                    );
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->storeManager->setCurrentStore(null);
            throw new GraphQlInputException(
                __("The store that was requested wasn't found. Verify the store and try again.")
            );
        }
    }
}
