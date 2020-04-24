<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

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
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * ConfirmByToken constructor.
     *
     * @param GetCustomerByToken $getByToken
     * @param CustomerResource $customerResource
     */
    public function __construct(
        GetCustomerByToken $getByToken,
        CustomerResource $customerResource
    ) {
        $this->getByToken = $getByToken;
        $this->customerResource = $customerResource;
    }

    /**
     * Confirm customer account my rp_token
     *
     * @param string $resetPasswordToken
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $resetPasswordToken): void
    {
        $customer = $this->getByToken->execute($resetPasswordToken);
        if ($customer->getConfirmation()) {
            $this->customerResource->updateColumn($customer->getId(), 'confirmation', null);
        }
    }
}
