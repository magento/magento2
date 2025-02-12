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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface;

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
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param ResolverInterface $localeResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        StoreCookieManagerInterface $storeCookieManager,
        ?ResolverInterface $localeResolver = null,
        ?LoggerInterface $logger = null
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->storeCookieManager = $storeCookieManager;
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(ResolverInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Handle the value of the store and set the scope
     *
     * @see \Magento\Store\App\Action\Plugin\Context::beforeDispatch
     *
     * @param string $headerValue
     * @return void
     */
    public function processHeaderValue(string $headerValue): void
    {
        if (!empty($headerValue)) {
            $storeCode = ltrim(rtrim($headerValue));
            try {
                $this->localeResolver->emulate($this->storeManager->getStore($storeCode)->getId());
                // $this->storeManager->getStore($storeCode) throws error with non existing stores
                // and logged in the catch
                $this->storeManager->setCurrentStore($storeCode);
                $this->updateContext($storeCode);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
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
    private function updateContext(string $storeCode): void
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
