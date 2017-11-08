<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\QuoteRepository\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
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
            $customer = $this->customerRepository->getById($quote->getCustomerId());
            foreach ($this->fields as $property) {
                if ($quote->getData($property) === null) {
                    $value = $this->getCustomerData($customer, $property);
                    $quote->setData($property, $value);
                }
            }
            $quote->setCustomerGroupId($customer->getGroupId());
            $quote->setCustomerIsGuest(false);
        }

        return null;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $property
     *
     * @return null|string
     */
    private function getCustomerData(CustomerInterface $customer, string $property)
    {
        $result = '';
        switch ($property) {
            case OrderInterface::CUSTOMER_EMAIL:
                $result = $customer->getEmail();
                break;
            case OrderInterface::CUSTOMER_FIRSTNAME:
                $result = $customer->getFirstname();
                break;
            case OrderInterface::CUSTOMER_LASTNAME:
                $result = $customer->getLastname();
                break;
        }

        return $result;
    }
}
