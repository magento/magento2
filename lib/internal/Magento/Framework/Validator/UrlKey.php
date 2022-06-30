<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

/**
 * Class UrlKey performs validation for reserved endpoint names
 */
class UrlKey
{
    /**
     * @var array
     */
    private $restrictedValues = [];

    /**
     * @param array $restrictedValues
     */
    public function __construct(
        array $restrictedValues
    ) {
        $this->restrictedValues = $restrictedValues;
    }

    /**
     * Validates that urlkey not belongs to reserved endpoints
     *
     * @param string|null $urlKey
     * @return bool
     */
    public function isValid(?string $urlKey): bool
    {
        if (in_array($urlKey, $this->restrictedValues)) {
            return false;
        }

        return true;
    }

    /**
     * Returns array of reserved endpoints
     *
     * @return array
     */
    public function getRestrictedValues(): array
    {
        return $this->restrictedValues;
    }
}
