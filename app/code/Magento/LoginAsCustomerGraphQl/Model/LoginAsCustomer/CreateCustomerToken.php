<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerGraphQl\Model\LoginAsCustomer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Store\Api\Data\StoreInterface;
use Exception;

/**
 * Create customer token from customer email
 */
class CreateCustomerToken
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @param TokenFactory $tokenModelFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        TokenFactory $tokenModelFactory,
        CustomerFactory $customerFactory
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerFactory= $customerFactory;
    }

    /**
     * Get admin user token
     *
     * @param string $email
     * @param StoreInterface $store
     * @return array
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function execute(string $email, StoreInterface $store): array
    {
        $customer = $this->customerFactory->create()->setWebsiteId((int)$store->getId())->loadByEmail($email);

        /* Check if customer email exist */
        if (!$customer->getId()) {
            throw new GraphQlInputException(
                __('Customer email provided does not exist')
            );
        }

        try {
            return [
                "customer_token" => $this->tokenModelFactory->create()
                    ->createCustomerToken($customer->getId())->getToken()
            ];
        } catch (Exception $e) {
            throw new LocalizedException(
                __(
                    'Unable to generate tokens. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }
}
