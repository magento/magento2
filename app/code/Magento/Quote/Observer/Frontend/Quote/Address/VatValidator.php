<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

/**
 * Class \Magento\Quote\Observer\Frontend\Quote\Address\VatValidator
 *
 */
class VatValidator
{
    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddress;

    /**
     * Customer VAT
     *
     * @var \Magento\Customer\Model\Vat
     */
    protected $customerVat;

    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Customer\Model\Vat $customerVat
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Customer\Model\Vat $customerVat
    ) {
        $this->customerVat = $customerVat;
        $this->customerAddress = $customerAddress;
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
            $quoteAddress->setVatIsValid((int)$validationResult->getIsValid());
            $quoteAddress->setVatRequestId($validationResult->getRequestIdentifier());
            $quoteAddress->setVatRequestDate($validationResult->getRequestDate());
            $quoteAddress->setVatRequestSuccess($validationResult->getRequestSuccess());
            $quoteAddress->setValidatedVatNumber($customerVatNumber);
            $quoteAddress->setValidatedCountryCode($customerCountryCode);
            $quoteAddress->save();
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
