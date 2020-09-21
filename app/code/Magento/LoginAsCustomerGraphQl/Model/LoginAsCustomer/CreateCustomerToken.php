<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerGraphQl\Model\LoginAsCustomer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Create customer token from customer email
 *
 * Class CreateCustomerToken
 */
class CreateCustomerToken
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CreateCustomerToken constructor.
     * @param StoreManagerInterface $storeManager
     * @param TokenFactory $tokenModelFactory
     * @param Customer $customer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TokenFactory $tokenModelFactory,
        Customer $customer
    ) {
        $this->storeManager = $storeManager;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customer= $customer;
    }

    /**
     * @param string $email
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $email)
    {
        $websiteID = $this->storeManager->getStore()->getWebsiteId();
        $this->customer->setWebsiteId($websiteID)->loadByEmail($email);

        /* Check if customer email exist */
        if (!$this->customer->getId()) {
            throw new GraphQlInputException(
                __('Customer email provided does not exist')
            );
        }

        $customerId = $this->customer->getId();
        $customerToken = $this->tokenModelFactory->create();
        return [
            "customer_token" => $customerToken->createCustomerToken($customerId)->getToken()
        ];
    }
}
