<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator\Pattern;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Framework\Validator\EmailAddress;

/**
 * Email fields pattern validator.
 */
class EmailAddressValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * Pattern for email validation.
     *
     * @var string
     */
    public string $patterEmail = '/^[^@]+@[^@]+\.[a-zA-Z]{2,}$/';

    /**
     * XML path for global security pattern validation.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_ENABLED = 'system/security_pattern/security_pattern';
    
    /**
     * XML path for email validation enablement.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_EMAIL_ENABLED = 'system/security_pattern/security_pattern_mail';
    
    /**
     * XML path for email blacklist validation.
     *
     * @var string
     */
    private const XML_PATH_SECURITY_PATTERN_MAIL_BLACKLIST = 'system/security_pattern/security_pattern_mail_list';

    /**
     * Description of allowed characters for email fields.
     *
     * @var string
     */
    public string $allowedCharsDescription = 'A-Z, a-z, 0-9, ., _, %, +, -, @';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EmailAddress
     */
    private EmailAddress $emailValidator;
    
    /**
     * @var array|null
     */
    private ?array $blacklistArray = null;

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EmailAddress $emailValidator
     */
    public function __construct(ScopeConfigInterface $scopeConfig, EmailAddress $emailValidator)
    {
        $this->scopeConfig = $scopeConfig;
        $this->emailValidator = $emailValidator;
    }

    /**
     * Check if both the global security pattern and email validation are enabled in the configuration.
     *
     * @return bool
     */
    public function isValidationEnabled(): bool
    {
        $isGlobalPatternEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $isEmailValidationEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_EMAIL_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        return $isGlobalPatternEnabled && $isEmailValidationEnabled;
    }

    /**
     * Validate an email address.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if ($value === null || $value === '' || !is_string($value)) {
            return false;
        }

        if (!preg_match($this->patterEmail, trim($value))) {
            return false;
        }

        return $this->emailValidator->isValid($value);
    }

    /**
     * Check if the email address or its domain is blacklisted.
     *
     * @param string|null $emailValue
     * @return bool
     */
    public function isBlacklist(?string $emailValue): bool
    {
        if ($emailValue === null || $emailValue === '' || !is_string($emailValue)) {
            return false;
        }

        if ($this->blacklistArray === null) {
            $blacklist = $this->scopeConfig->getValue(self::XML_PATH_SECURITY_PATTERN_MAIL_BLACKLIST);
            $this->blacklistArray = !empty($blacklist) ? preg_split('/[\r\n,]+/', $blacklist) : [];
        }

        $emailHost = substr(strrchr($emailValue, "@"), 1);

        return in_array($emailValue, $this->blacklistArray) || in_array($emailHost, $this->blacklistArray);
    }
}
