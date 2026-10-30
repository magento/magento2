<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\User\Model\UserValidationRules;

/**
 * Password configuration view model
 */
class PasswordConfig implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get minimum password length from configuration
     *
     * @return int
     */
    public function getMinimumPasswordLength(): int
    {
        return (int) $this->scopeConfig->getValue('admin/security/minimum_password_length') ?:
            UserValidationRules::MIN_PASSWORD_LENGTH;
    }
}
