<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\ValidationRules\ShippingMethodValidationRule;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

class ShippingMethodValidationRulePlugin
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param AddressFactory|null $addressFactory
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ?AddressFactory $addressFactory = null
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->addressFactory = $addressFactory ?? ObjectManager::getInstance()->get(AddressFactory::class);
    }

    /**
     * After plugin for validate method to ensure shipping method validity.
     *
     * @param ShippingMethodValidationRule $subject
     * @param ValidationResult[] $result
     * @param Quote $quote
     * @return ValidationResult[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterValidate(
        ShippingMethodValidationRule $subject,
        array $result,
        Quote $quote
    ): array {
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress || $quote->isVirtual()) {
            return $result;
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        $shippingRate = $shippingMethod ? $shippingAddress->getShippingRateByCode($shippingMethod) : null;
        $validationResult = $shippingMethod && $shippingRate && $this->isShippingMethodAvailable($shippingAddress);

        if ($validationResult) {
            return $result;
        }

        $existing = $result[0] ?? null;
        if ($existing instanceof ValidationResult && $existing->isValid()) {
            $result[0] = $this->validationResultFactory->create([
                'errors' => [__('The shipping method is missing. Select the shipping method and try again')]
            ]);
        }

        return $result;
    }

    /**
     * Check that carriers still offer the selected shipping method
     *
     * Address::requestShippingRates() writes the raw carrier price into the shipping amounts and adds
     * the collected rates to the address, so the rates are requested on a copy that is never saved.
     *
     * @param Address $shippingAddress
     * @return bool
     */
    private function isShippingMethodAvailable(Address $shippingAddress): bool
    {
        $address = $this->addressFactory->create();
        $address->setData($shippingAddress->getData());
        $address->unsetData($address->getIdFieldName());
        $address->setQuote($shippingAddress->getQuote());

        return (bool) $address->requestShippingRates();
    }
}
