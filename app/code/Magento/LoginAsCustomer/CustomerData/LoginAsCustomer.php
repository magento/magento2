<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;

/**
 * Customer data for the logged_as_customer section
 */
class LoginAsCustomer implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * LoginAsCustomer constructor.
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * Retrieve private customer data for the logged_as_customer section
     * @return array
     */
    public function getSectionData():array
    {
        if (!$this->customerSession->getCustomerId()) {
            return [];
        }

        return [
            'admin_user_id' => $this->customerSession->getLoggedAsCustomerAdmindId(),
        ];
    }
}
