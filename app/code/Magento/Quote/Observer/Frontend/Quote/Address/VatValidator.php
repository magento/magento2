<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\ResourceModel\UpdateValidationResult;

class VatValidator
{
    /**
     * Customer address
     *
     * @var Address
     */
    protected $customerAddress;

    /**
     * Customer VAT
     *
     * @var Vat
     */
    protected $customerVat;

    /**
     * @var UpdateValidationResult|null
     */
    private $updateValidationResult;

    /**
     * @param Address $customerAddress
     * @param Vat $customerVat
     */
    public function __construct(
        Address $customerAddress,
        Vat $customerVat,
        UpdateValidationResult $updateValidationResult = null
    ) {
        $this->customerVat = $customerVat;
        $this->customerAddress = $customerAddress;
        $this->updateValidationResult = $updateValidationResult;

        // Backward compatibility object injection
        if ($this->updateValidationResult === null) {
            $this->updateValidationResult = ObjectManager::getInstance()->get(UpdateValidationResult::class);
        }
    }

    /**
     * Validate VAT number
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param \Magento\Store\Model\Store|int $store
     * @return \Magento\Framework\DataObject
     */
    public function validate(\Magento\Quote\Model\Quote\Address $quoteAddress, $store)
    {
        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();

        $merchantCountryCode = $this->customerVat->getMerchantCountryCode();
        $merchantVatNumber = $this->customerVat->getMerchantVatNumber();

        $validationResult = null;
        if ($this->customerAddress->hasValidateOnEachTransaction(
            $store
        ) ||
            $customerCountryCode != $quoteAddress->getValidatedCountryCode() ||
            $customerVatNumber != $quoteAddress->getValidatedVatNumber()
        ) {
            // Send request to gateway
            $validationResult = $this->customerVat->checkVatNumber(
                $customerCountryCode,
                $customerVatNumber,
                $merchantVatNumber !== '' ? $merchantCountryCode : '',
                $merchantVatNumber
            );

            // Store validation results in corresponding quote address
            $quoteAddress->setValidatedVatNumber($customerVatNumber);
            $quoteAddress->setValidatedCountryCode($customerCountryCode);

            $this->updateValidationResult->execute($quoteAddress, $validationResult);
        } else {
            // Restore validation results from corresponding quote address
            $validationResult = new \Magento\Framework\DataObject(
                [
                    'is_valid' => (int)$quoteAddress->getVatIsValid(),
                    'request_identifier' => (string)$quoteAddress->getVatRequestId(),
                    'request_date' => (string)$quoteAddress->getVatRequestDate(),
                    'request_success' => (bool)$quoteAddress->getVatRequestSuccess(),
                ]
            );
        }

        return $validationResult;
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param \Magento\Store\Model\Store|int $store
     * @return bool
     */
    public function isEnabled(\Magento\Quote\Model\Quote\Address $quoteAddress, $store)
    {
        $configAddressType = $this->customerAddress->getTaxCalculationAddressType($store);

        // When VAT is based on billing address then Magento have to handle only billing addresses
        $additionalBillingAddressCondition = $configAddressType ==
            \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING ? $configAddressType !=
            $quoteAddress->getAddressType() : false;

        // Handle only addresses that corresponds to VAT configuration
        if (!$this->customerAddress->isVatValidationEnabled($store) || $additionalBillingAddressCondition) {
            return false;
        }

        return true;
    }
}
