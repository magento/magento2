<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

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
     * Handle the value of the store and set the scope
     *
     * @inheritDoc
     * @throws GraphQlInputException
     */
    public function processHeaderValue(string $headerValue, HttpRequestInterface $request) : void
    {
        if ($headerValue) {
            $storeCode = ltrim(rtrim($headerValue));
            $stores = $this->storeManager->getStores(false, true);
            if (isset($stores[$storeCode])) {
                $this->storeManager->setCurrentStore($storeCode);
                $this->updateContext($storeCode);
            } elseif (strtolower($storeCode) !== 'default') {
                throw new GraphQlInputException(
                    new \Magento\Framework\Phrase('Store code %1 does not exist', [$storeCode])
                );
            }
        }
    }

    /**
     * Update context accordingly to the store code found.
     *
     * @param string $store
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
}
