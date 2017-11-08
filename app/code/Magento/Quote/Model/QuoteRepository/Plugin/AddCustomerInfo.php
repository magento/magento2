<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\QuoteRepository\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Add to quote all necessary customer information.
 */
class AddCustomerInfo
{
    /**
     * List of necessary fields.
     *
     * @var array
     */
    private $fields = [
        OrderInterface::CUSTOMER_EMAIL,
        OrderInterface::CUSTOMER_FIRSTNAME,
        OrderInterface::CUSTOMER_LASTNAME,
        OrderInterface::CUSTOMER_GROUP_ID,
    ];

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * AddCustomerInfo constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add to quote customer necessary information, if needed.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        if ($quote->getCustomerId() !== null) {
            foreach ($this->fields as $property) {
                if (!$quote->getData($property)) {
                    $customer = $this->customerRepository->getById($quote->getCustomerId());
                    $quote->setCustomer($customer);
                    $quote->setCustomerIsGuest(false);
                    break;
                }
            }
        }

        return null;
    }
}
