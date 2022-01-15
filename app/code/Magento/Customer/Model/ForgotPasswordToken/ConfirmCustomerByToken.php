<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Confirm customer by reset password token
 */
class ConfirmCustomerByToken
{
    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param GetCustomerByToken $getByToken
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(GetCustomerByToken $getByToken, CustomerRepositoryInterface $customerRepository)
    {
        $this->getByToken = $getByToken;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Confirm customer account my rp_token
     *
     * @param string $resetPasswordToken
     * @return void
     * @throws LocalizedException
     */
    public function execute(string $resetPasswordToken): void
    {
        $customer = $this->getByToken->execute($resetPasswordToken);
        if ($customer->getConfirmation()) {
            $this->resetConfirmation($customer);
        }
    }

    /**
     * Reset customer confirmation
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function resetConfirmation(CustomerInterface $customer): void
    {
        // skip unnecessary address and customer validation
        $customer->setData('ignore_validation_flag', true);
        $customer->setConfirmation(null);

        $this->customerRepository->save($customer);
    }

    /**
     * Check if customer confirmation needs to be reset
     *
     * @param int $customerId
     * @return void
     */
    public function resetCustomerConfirmation(int $customerId): void
    {
        $customer = $this->customerRepository->getById($customerId);

        if ($customer) {
            $this->resetConfirmation($customer);
        }
    }
}
