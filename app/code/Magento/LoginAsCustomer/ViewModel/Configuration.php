<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\ViewModel;

use Magento\Customer\Model\Context;

/**
 * View model to get extension configuration in the template
 */
class Configuration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Magento\LoginAsCustomer\Model\Config
     */
    private $config;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * Configuration constructor.
     * @param \Magento\LoginAsCustomer\Model\Config $config
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\LoginAsCustomer\Model\Config $config,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->config = $config;
        $this->httpContext = $httpContext;
    }

    /**
     * Retrieve true if login as a customer is enabled
     * @return bool
     */
    public function isEnabled():bool
    {
        return $this->config->isEnabled() && $this->isLoggedIn();
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    private function isLoggedIn():bool
    {
        return (bool)$this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
