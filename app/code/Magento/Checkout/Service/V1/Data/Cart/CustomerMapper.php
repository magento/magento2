<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

use Magento\Sales\Model\Quote;

/**
 * Cart mapper
 */
class CustomerMapper
{
    /**
     * Fetch quote customer data
     *
     * @param Quote $quote
     * @return array
     */
    public function map(Quote $quote)
    {
        return [
            Customer::ID => $quote->getCustomerId(),
            Customer::EMAIL => $quote->getCustomerEmail(),
            Customer::GROUP_ID => $quote->getCustomerGroupId(),
            Customer::TAX_CLASS_ID => $quote->getCustomerTaxClassId(),
            Customer::PREFIX => $quote->getCustomerPrefix(),
            Customer::FIRST_NAME => $quote->getCustomerFirstname(),
            Customer::MIDDLE_NAME => $quote->getCustomerMiddlename(),
            Customer::LAST_NAME => $quote->getCustomerLastname(),
            Customer::SUFFIX => $quote->getCustomerSuffix(),
            Customer::DOB => $quote->getCustomerDob(),
            Customer::NOTE => $quote->getCustomerNote(),
            Customer::NOTE_NOTIFY => $quote->getCustomerNoteNotify(),
            Customer::IS_GUEST => $quote->getCustomerIsGuest(),
            Customer::GENDER => $quote->getCustomerGender(),
            Customer::TAXVAT => $quote->getCustomerTaxvat()
        ];
    }
}
