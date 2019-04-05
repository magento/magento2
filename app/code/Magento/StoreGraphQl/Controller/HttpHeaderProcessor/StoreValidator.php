<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;

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
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        StoreCookieManagerInterface $storeCookieManager
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->storeCookieManager = $storeCookieManager;
    }

    /**
     * Validate the header 'Store' value.
     *
     * @param HttpRequestInterface $request
     * @return void
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
                        __("The store that was requested wasn't found. Verify the store and try again.")
                    );
                }
            }
        }
    }
}
