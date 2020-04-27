<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer data for the logged_as_customer section
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class LoginAsCustomer implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LoginAsCustomer constructor.
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve private customer data for the logged_as_customer section
     *
     * @return array
     */
    public function getSectionData():array
    {
        if (!$this->customerSession->getCustomerId()) {
            return [];
        }

        return [
            'adminUserId' => $this->customerSession->getLoggedAsCustomerAdmindId(),
            'websiteName' => $this->storeManager->getWebsite()->getName()
        ];
    }
}
