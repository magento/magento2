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
namespace Magento\Checkout\Service\V1\Data\Cart;

use \Magento\Sales\Model\Quote;

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
