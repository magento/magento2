<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Controller\HttpRequestValidator;

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
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request): void
    {
        try {
            $headerValue = $request->getHeader('Content-Currency');
            if (!empty($headerValue)) {
                $headerCurrency = strtoupper(ltrim(rtrim($headerValue)));
                /** @var \Magento\Store\Model\Store $currentStore */
                $currentStore = $this->storeManager->getStore();
                if (!in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes(true))) {
                    throw new GraphQlInputException(
                        __('Please correct the target currency')
                    );
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->storeManager->setCurrentStore(null);
            throw new GraphQlInputException(
                __("Requested store is not found")
            );
        }
    }
}
