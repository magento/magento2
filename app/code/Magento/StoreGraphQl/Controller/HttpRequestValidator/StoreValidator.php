<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Validate the "Store" header entry
 */
class StoreValidator implements HttpRequestValidatorInterface
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
     * Validate the header 'Store' value.
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request): void
    {
        $headerValue = $request->getHeader('Store');
        if (!empty($headerValue)) {
            $storeCode = ltrim(rtrim($headerValue));
            $stores = $this->storeManager->getStores(false, true);
            if (!isset($stores[$storeCode])) {
                if (strtolower($storeCode) !== 'default') {
                    $this->storeManager->setCurrentStore(null);
                    throw new GraphQlInputException(
                        __("Requested store is not found")
                    );
                }
            }
        }
    }
}
