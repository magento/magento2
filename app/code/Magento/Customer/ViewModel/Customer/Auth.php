<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer's auth view model
 */
class Auth implements ArgumentInterface
{
    /**
     * @param HttpContext $httpContext
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private HttpContext $httpContext,
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check is user login
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH) ?? false;
    }

    /**
     * Get customer account share scope
     *
     * @return int
     */
    public function getCustomerShareScope(): int
    {
        return (int) $this->scopeConfig->getValue(
            'customer/account_share/scope',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
