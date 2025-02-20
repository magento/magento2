<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\ViewModel;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigInterface $config
     * @param HttpContext $httpContext
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        ConfigInterface $config,
        HttpContext $httpContext,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ?GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId = null
    ) {
        $this->config = $config;
        $this->httpContext = $httpContext;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId
            ?? ObjectManager::getInstance()->get(GetLoggedAsCustomerAdminIdInterface::class);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
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

    /**
     * Is B2b enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isB2bEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            "btob/website_configuration/company_active",
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore()->getWebsiteId()
        );
    }
}
