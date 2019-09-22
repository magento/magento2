<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Controller\HttpHeaderProcessor;

use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;

/**
 * Process the "Store" header entry
 */
class StoreProcessor implements HttpHeaderProcessorInterface
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
     * @param StoreCookieManagerInterface $storeCookieManager
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
     * Handle the value of the store and set the scope
     *
     * @see \Magento\Store\App\Action\Plugin\Context::beforeDispatch
     *
     * @param string $headerValue
     * @return void
     */
    public function processHeaderValue(string $headerValue) : void
    {
        if (!empty($headerValue)) {
            $storeCode = ltrim(rtrim($headerValue));
            $this->storeManager->setCurrentStore($storeCode);
            $this->updateContext($storeCode);
        } elseif (!$this->isAlreadySet()) {
            $storeCode = $this->storeCookieManager->getStoreCodeFromCookie()
                ?: $this->storeManager->getDefaultStoreView()->getCode();
            $this->storeManager->setCurrentStore($storeCode);
            $this->updateContext($storeCode);
        }
    }

    /**
     * Update context accordingly to the store code found.
     *
     * @param string $storeCode
     * @return void
     */
    private function updateContext(string $storeCode) : void
    {
        $this->httpContext->setValue(
            StoreManagerInterface::CONTEXT_STORE,
            $storeCode,
            $this->storeManager->getDefaultStoreView()->getCode()
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
