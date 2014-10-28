<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote\Address;

class VatValidator
{
    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddress;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $customerData;

    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Customer\Helper\Data $customerData
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Customer\Helper\Data $customerData
    ) {
        $this->customerData = $customerData;
        $this->customerAddress = $customerAddress;
    }

    /**
     * Validate VAT number
     *
     * @param \Magento\Sales\Model\Quote\Address $quoteAddress
     * @param \Magento\Store\Model\Store|int $store
     * @return \Magento\Framework\Object
     */
    public function validate(\Magento\Sales\Model\Quote\Address $quoteAddress, $store)
    {
        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();

        $merchantCountryCode = $this->customerData->getMerchantCountryCode();
        $merchantVatNumber = $this->customerData->getMerchantVatNumber();

        $validationResult = null;
        if ($this->customerAddress->hasValidateOnEachTransaction(
            $store
        ) ||
            $customerCountryCode != $quoteAddress->getValidatedCountryCode() ||
            $customerVatNumber != $quoteAddress->getValidatedVatNumber()
        ) {
            // Send request to gateway
            $validationResult = $this->customerData->checkVatNumber(
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
            $validationResult = new \Magento\Framework\Object(
                array(
                    'is_valid' => (int)$quoteAddress->getVatIsValid(),
                    'request_identifier' => (string)$quoteAddress->getVatRequestId(),
                    'request_date' => (string)$quoteAddress->getVatRequestDate(),
                    'request_success' => (bool)$quoteAddress->getVatRequestSuccess()
                )
            );
        }

        return $validationResult;
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Sales\Model\Quote\Address $quoteAddress
     * @param \Magento\Store\Model\Store|int $store
     * @return bool
     */
    public function isEnabled(\Magento\Sales\Model\Quote\Address $quoteAddress, $store)
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
