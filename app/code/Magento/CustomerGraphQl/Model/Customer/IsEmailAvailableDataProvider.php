<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\AccountManagementInterface;

/**
 * Is Customer Email Available checker
 */
class IsEmailAvailableDataProvider
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(AccountManagementInterface $accountManagement)
    {
        $this->accountManagement = $accountManagement;
    }

    /**
     * Check is Email available
     *
     * @param string $email
     * @return bool
     */
    public function execute(string $email): bool
    {
        return $this->accountManagement->isEmailAvailable($email);
    }
}
