<?php
/**
<<<<<<< HEAD
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

=======
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Delegating account actions from outside of customer module.
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
