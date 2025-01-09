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

/**
 * Code injection fields pattern validator.
 */
class ForbiddenValidator extends AbstractValidator
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * XML path for forbidden pattern validation enablement.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_CODE_INJECTION_ENABLED = 'system/security_pattern/security_code_injection';

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if forbidden patterns validation is enabled.
     *
     * @return bool
     */
    public function isValidationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_CODE_INJECTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns an array of forbidden patterns.
     *
     * @return string[]
     */
    public function getPatterns(): array
    {
        return [
            '/{{.*}}/',
            '/<\?=/',
            '/<\?php/',
            '/shell_exec/',
            '/eval\(/',
            '/\${IFS%/',
            '/\bcurl\b/',
        ];
    }

    /**
     * Validates the given field value against forbidden patterns.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (!$this->isValidationEnabled()) {
            return true;
        }

        return $this->validatePattern($value);
    }

    /**
     * Recursively validate data against forbidden patterns.
     *
     * @param mixed $data
     * @return bool
     */
    public function validateDataRecursively($data): bool
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                if (!$this->validateDataRecursively($value)) {
                    return false;
                }
            }
        } else {
            return $this->isValid($data);
        }

        return true;
    }

    /**
     * Validates the field value against forbidden patterns.
     *
     * @param mixed $value
     * @return bool
     */
    private function validatePattern(mixed $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return true;
        }

        foreach ($this->getPatterns() as $pattern) {
            if (preg_match($pattern, trim($value))) {
                return false;
            }
        }

        if (preg_match('/base64_decode\(/', $value)) {
            // Use of base64_decode is discouraged, ensure this is safe in your context
            // @codingStandardsIgnoreLine
            $decodedValue = base64_decode($value);
            return $this->validatePattern($decodedValue);
        }

        return true;
    }
}
