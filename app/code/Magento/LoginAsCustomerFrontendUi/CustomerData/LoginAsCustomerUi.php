<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer data for the logged_as_customer section
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class LoginAsCustomerUi implements SectionSourceInterface
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
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        ?GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId = null
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId
            ?? ObjectManager::getInstance()->get(GetLoggedAsCustomerAdminIdInterface::class);
    }

    /**
     * Retrieve private customer data for the logged_as_customer section
     *
     * @return array
     * @throws LocalizedException
     */
    public function getSectionData(): array
    {
        $adminId = $this->getLoggedAsCustomerAdminId->execute();

        if (!$adminId || !$this->customerSession->getCustomerId()) {
            return [];
        }

        return [
            'adminUserId' => $adminId,
            'websiteName' => $this->storeManager->getWebsite()->getName()
        ];
    }
}
