<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\Framework\Exception\LocalizedException;

class ImsCommandValidationService
{
    /**
     * @var string
     */
    private string $organizationIdRegex;

    /**
     * @var string
     */
    private string $clientIdRegex;

    /**
     * @var string
     */
    private string $clientSecretRegex;

    /**
     * @var string
     */
    private string $twoFactorAuthRegex;

    /**
     * @param string $organizationIdRegex
     * @param string $clientIdRegex
     * @param string $clientSecretRegex
     * @param string $twoFactorAuthRegex
     */
    public function __construct(
        string $organizationIdRegex,
        string $clientIdRegex,
        string $clientSecretRegex,
        string $twoFactorAuthRegex
    ) {
        $this->organizationIdRegex = $organizationIdRegex;
        $this->clientIdRegex = $clientIdRegex;
        $this->clientSecretRegex = $clientSecretRegex;
        $this->twoFactorAuthRegex = $twoFactorAuthRegex;
    }

    /**
     * Validate that value is not empty
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    private function emptyValueValidator(string $value): string
    {
        if (trim($value) === '') {
            throw new LocalizedException(
                __('This field is required to enable the Admin Adobe IMS Module')
            );
        }

        return trim($value);
    }

    /**
     * Validate Organization ID
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function organizationIdValidator(string $value): string
    {
        $value = $this->emptyValueValidator($value);

        /** @todo: use this for ImsOrganizationService::validateAndExtractOrganizationId() */
        if (preg_match($this->organizationIdRegex, $value, $match)
            && isset($match[1])
        ) {
            return $match[1];
        }

        throw new LocalizedException(
            __('No valid Organization ID provided')
        );
    }

    /**
     * Validate Client ID
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function clientIdValidator(string $value): string
    {
        $value = $this->emptyValueValidator($value);

        if (preg_match($this->clientIdRegex, $value)) {
            throw new LocalizedException(
                __('No valid Client ID provided')
            );
        }

        return $value;
    }

    /**
     * Validate Client Secret
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function clientSecretValidator(string $value): string
    {
        $value = $this->emptyValueValidator($value);

        if (preg_match($this->clientSecretRegex, $value)) {
            throw new LocalizedException(
                __('No valid Client Secret provided')
            );
        }

        return $value;
    }

    /**
     * Validate Two-Factor Auth enabled state
     *
     * @param string $value
     * @return bool
     * @throws LocalizedException
     */
    public function twoFactorAuthValidator(string $value): bool
    {
        $value = $this->emptyValueValidator($value);

        if (preg_match($this->twoFactorAuthRegex, $value)) {
            return true;
        }

        return false;
    }
}
