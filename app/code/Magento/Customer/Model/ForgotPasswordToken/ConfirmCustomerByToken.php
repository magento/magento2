<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;

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
     * ConfirmByToken constructor.
     *
     * @param GetCustomerByToken $getByToken
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        GetCustomerByToken $getByToken,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->getByToken = $getByToken;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Confirm customer account my rp_token
     *
     * @param string $resetPasswordToken
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated rp tokens cannot be looked up directly from db
     */
    public function execute(string $resetPasswordToken): void
    {
        $customer = $this->getByToken->execute($resetPasswordToken);
        if ($customer->getConfirmation()) {
            $this->customerRepository->save(
                $customer->setConfirmation(null)
            );
        }
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

        if ($customer && $customer->getConfirmation()) {
            $this->customerRepository->save(
                $customer->setConfirmation(null)
            );
        }
    }
}
