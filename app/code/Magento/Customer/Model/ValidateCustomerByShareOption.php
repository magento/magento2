<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Validate Customer By Share Option.
 */
class ValidateCustomerByShareOption
{
    /**
     * @var Config\Share
     */
    private $share;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @param Config\Share $share
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Share $share,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        AuthenticationInterface $authentication
    ) {
        $this->share = $share;
        $this->storeManager = $storeManager;
        $this->authentication = $authentication;
        $this->customerRepository = $customerRepository;
    }

    /**
     * If website scope for customer check if customer from current website and isn't locked.
     *
     * @param int $customerId
     *
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        $result = true;
        if ((bool)$this->share->isWebsiteScope() === true) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->customerRepository->getById($customerId);

            if ((int)$customer->getWebsiteId() !== (int)$websiteId
                || true === $this->authentication->isLocked($customerId)
            ) {
                $result = false;
            }
        } else {
            if (true === $this->authentication->isLocked($customerId)) {
                $result = false;
            }
        }

        return $result;
    }
}
