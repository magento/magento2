<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\ViewModel;

use Magento\Customer\Model\Context;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * View model to get extension configuration in the template
 */
class Configuration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @param ConfigInterface $config
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->config = $config;
        $this->httpContext = $httpContext;
    }

    /**
     * Retrieve true if login as a customer is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->isLoggedIn();
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    private function isLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
