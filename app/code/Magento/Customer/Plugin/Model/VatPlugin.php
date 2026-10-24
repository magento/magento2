<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Plugin\Model;

use Magento\Customer\Model\Vat;
use Magento\Customer\Model\VatValidationResultStorage;

/**
 * Plugin to cache the VAT number validation result based on provided VAT number and country code
 */
class VatPlugin
{
    /**
     * @param VatValidationResultStorage $vatValidationResultStorage
     */
    public function __construct(
        private readonly VatValidationResultStorage $vatValidationResultStorage
    ) {
    }

    /**
     * Cache VAT number validation result
     *
     * @param Vat $subject
     * @param \Closure $proceed
     * @param string $countryCode
     * @param string $vatNumber
     * @param string $requesterCountryCode
     * @param string $requesterVatNumber
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckVatNumber(
        Vat $subject,
        \Closure $proceed,
        $countryCode,
        $vatNumber,
        $requesterCountryCode = '',
        $requesterVatNumber = ''
    ) {
        $storedValidationResult = $this->vatValidationResultStorage->get($vatNumber, $countryCode);

        if ($storedValidationResult) {
            return $storedValidationResult;
        }
        $validationResult = $proceed($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber);

        if ($validationResult->getRequestSuccess()) {
            $this->vatValidationResultStorage->set($vatNumber, $countryCode, $validationResult);
        }

        return $validationResult;
    }
}
