<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerAssistance\Api\ConfigInterface as AssistanceConfigInterface;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

/**
 * View model for Login as Customer Shopping Assistance block.
 */
class ShoppingAssistanceViewModel implements ArgumentInterface
{
    /**
     * @var AssistanceConfigInterface
     */
    private $assistanceConfig;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IsAssistanceEnabled
     */
    private $isAssistanceEnabled;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param AssistanceConfigInterface $assistanceConfig
     * @param ConfigInterface $config
     * @param IsAssistanceEnabled $isAssistanceEnabled
     * @param Session $session
     */
    public function __construct(
        AssistanceConfigInterface $assistanceConfig,
        ConfigInterface $config,
        IsAssistanceEnabled $isAssistanceEnabled,
        Session $session
    ) {
        $this->assistanceConfig = $assistanceConfig;
        $this->config = $config;
        $this->isAssistanceEnabled = $isAssistanceEnabled;
        $this->session = $session;
    }

    /**
     * Is Login as Customer functionality enabled.
     *
     * @return bool
     */
    public function isLoginAsCustomerEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Is merchant assistance allowed by Customer.
     *
     * @return bool
     */
    public function isAssistanceAllowed(): bool
    {
        $customerId = $this->session->getId();

        return $customerId && $this->isAssistanceEnabled->execute((int)$customerId);
    }

    /**
     * Get shopping assistance checkbox title from config.
     *
     * @return string
     */
    public function getAssistanceCheckboxTitle()
    {
        return $this->assistanceConfig->getShoppingAssistanceCheckboxTitle();
    }

    /**
     * Get shopping assistance checkbox tooltip text from config.
     *
     * @return string
     */
    public function getAssistanceCheckboxTooltip()
    {
        return $this->assistanceConfig->getShoppingAssistanceCheckboxTooltip();
    }
}
