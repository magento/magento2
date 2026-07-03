<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Process the "Currency" header entry
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
     * @var Http
     */
    private $request;

    /**
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param SessionManagerInterface $session
     * @param LoggerInterface $logger
     * @param Http|null $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        SessionManagerInterface $session,
        LoggerInterface $logger,
        ?Http $request = null
    ) {
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->session = $session;
        $this->logger = $logger;
        $this->request = $request ?? ObjectManager::getInstance()->get(Http::class);
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
            $currentStore = $this->storeManager->getStore();
            $defaultCode = $currentStore->getDefaultCurrency()->getCode();
            if (empty($headerValue)) {
                $currencyCode = $this->request->isGet()
                    ? $defaultCode
                    : $currentStore->getCurrentCurrency()->getCode();
                $this->httpContext->setValue(
                    HttpContext::CONTEXT_CURRENCY,
                    $currencyCode,
                    $defaultCode
                );
            } else {
                $headerCurrency = strtoupper(trim($headerValue));
                if (!in_array($headerCurrency, $currentStore->getAvailableCurrencyCodes(true))) {
                    //skip store not found exception as it will be handled in graphql validation
                    $this->logger->warning(__('Currency not allowed for store %1', [$currentStore->getCode()]));
                }
                $this->httpContext->setValue(HttpContext::CONTEXT_CURRENCY, $headerCurrency, $defaultCode);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //skip store not found exception as it will be handled in graphql validation
            $this->logger->warning($e->getMessage());
        }
    }
}
