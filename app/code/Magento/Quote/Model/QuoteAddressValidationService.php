<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Address\Validator\AddressAttributeValidatorInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Checkout\Model\AddressComparatorInterface;

/**
 * Centralized address validation service for quotes.
 */
class QuoteAddressValidationService
{
    /**
     * @var AddressAttributeValidatorInterface
     */
    private $addressAttributeValidator;

    /**
     * @var AddressComparatorInterface
     */
    private $addressComparator;

    /**
     * @param AddressAttributeValidatorInterface $addressAttributeValidator
     * @param AddressComparatorInterface $addressComparator
     */
    public function __construct(
        AddressAttributeValidatorInterface $addressAttributeValidator,
        AddressComparatorInterface $addressComparator
    ) {
        $this->addressAttributeValidator = $addressAttributeValidator;
        $this->addressComparator = $addressComparator;
    }

    /**
     * Validate addresses using validation rules
     *
     * @param CartInterface $quote
     * @param AddressInterface|null $shippingAddress
     * @param AddressInterface|null $billingAddress
     * @return void
     * @throws InputException
     */
    public function validateAddressesWithRules(
        CartInterface $quote,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $billingAddress = null
    ): void {
        if (!$shippingAddress && !$billingAddress) {
            return;
        }

        // Get current addresses from quote for comparison
        $quoteShippingAddress = $quote->getShippingAddress();
        $quoteBillingAddress = $quote->getBillingAddress();

        // If shipping and billing addresses from request are the same, validate only once
        $addressesAreSame = $this->areAddressesEqual($shippingAddress, $billingAddress);
        if ($addressesAreSame) {
            // Only validate if different from quote shipping address
            if (!$this->areAddressesEqual($shippingAddress, $quoteShippingAddress)) {
                $this->validateSingleAddress($shippingAddress, 'shipping');
            }
            return;
        }

        // Validate shipping address only if it's different from quote shipping address
        $shouldValidateShipping = $shippingAddress
            && !$this->areAddressesEqual($shippingAddress, $quoteShippingAddress);

        if ($shouldValidateShipping) {
            $this->validateSingleAddress($shippingAddress, 'shipping');
        }

        // Validate billing address only if it's different from quote billing address
        $shouldValidateBilling = $billingAddress
            && !$this->areAddressesEqual($billingAddress, $quoteBillingAddress);

        if ($shouldValidateBilling) {
            $this->validateSingleAddress($billingAddress, 'billing');
        }
    }

    /**
     * Check if two addresses are equal
     *
     * @param AddressInterface|null $address1
     * @param AddressInterface|null $address2
     * @return bool
     */
    private function areAddressesEqual(?AddressInterface $address1, ?AddressInterface $address2): bool
    {
        return $address1 && $address2 && $this->addressComparator->isEqual($address1, $address2);
    }

    /**
     * Validate a single address if it meets validation criteria
     *
     * @param AddressInterface|null $address
     * @param string $addressType
     * @return bool Returns true if validation was performed
     * @throws InputException
     */
    private function validateSingleAddress(?AddressInterface $address, string $addressType): bool
    {
        if (!$address || !$this->isAddressComplete($address)) {
            return false;
        }

        if ($address->getShouldIgnoreValidation()) {
            return false;
        }

        $this->addressAttributeValidator->validate($address, $addressType);

        $address->setShouldIgnoreValidation(true);

        return true;
    }

    /**
     * Check if address has any data for validation
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function isAddressComplete(AddressInterface $address): bool
    {
        if (!$address || !$address->getCountryId()) {
            return false;
        }

        $hasEstimationFields = $this->hasEstimationFields($address);
        $hasPersonalOrAddressDetails = $this->hasPersonalOrAddressDetails($address);

        if ($hasEstimationFields && !$hasPersonalOrAddressDetails) {
            return false;
        }

        return $hasPersonalOrAddressDetails;
    }

    /**
     * Check if address has estimation fields (country + region/postcode)
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function hasEstimationFields(AddressInterface $address): bool
    {
        $countryId = $address->getCountryId();
        $regionId = $address->getRegionId();
        $region = $address->getRegion();
        $postcode = $address->getPostcode();

        return $countryId && ($regionId || $region || $postcode);
    }

    /**
     * Check if address has personal details or full address details
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function hasPersonalOrAddressDetails(AddressInterface $address): bool
    {
        return $this->hasPersonalDetails($address) || $this->hasAddressDetails($address);
    }

    /**
     * Check if address has personal details (firstname, lastname)
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function hasPersonalDetails(AddressInterface $address): bool
    {
        return !empty($address->getFirstname()) || !empty($address->getLastname());
    }

    /**
     * Check if address has full address details (street, city, telephone)
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function hasAddressDetails(AddressInterface $address): bool
    {
        return !empty($address->getCity()) || !empty($address->getTelephone());
    }
}
