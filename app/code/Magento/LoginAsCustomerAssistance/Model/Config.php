<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\LoginAsCustomerAssistance\Api\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class Config implements ConfigInterface
{
    private const XML_PATH_SHOPPING_ASSISTANCE_CHECKBOX_TITLE
        = 'login_as_customer/general/shopping_assistance_checkbox_title';
    private const XML_PATH_SHOPPING_ASSISTANCE_CHECKBOX_TOOLTIP
        = 'login_as_customer/general/shopping_assistance_checkbox_tooltip';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getShoppingAssistanceCheckboxTitle(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHECKBOX_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getShoppingAssistanceCheckboxTooltip(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHECKBOX_TOOLTIP,
            ScopeInterface::SCOPE_STORE
        );
    }
}
