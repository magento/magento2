<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Delegating account actions from outside of customer module.
 *
 * @api
 */
interface AccountDelegationInterface
{
    /**
     * Create redirect to default new account form.
     *
     * @param CustomerInterface $customer Pre-filled customer data.
     * @param array|null $mixedData Add this data to new-customer event
     * if the new customer is created.
     *
     * @return Redirect
     */
    public function createRedirectForNew(
        CustomerInterface $customer,
        array $mixedData = null
    ): Redirect;
}
