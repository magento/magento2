<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\ViewModel;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;

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
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param ConfigInterface $config
     * @param HttpContext $httpContext
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        ConfigInterface $config,
        HttpContext $httpContext,
        ?GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId = null
    ) {
        $this->config = $config;
        $this->httpContext = $httpContext;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId
            ?? ObjectManager::getInstance()->get(GetLoggedAsCustomerAdminIdInterface::class);
    }

    /**
     * Retrieve true if login as a customer is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->isLoggedIn() && $this->getLoggedAsCustomerAdminId->execute();
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
