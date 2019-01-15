<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\CustomerData;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\View;
use Magento\Persistent\Helper\Session;

/**
 * Customer persistent section
 */
class Persistent implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $persistentSession;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Session $persistentSession
     * @param View $customerViewHelper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Session $persistentSession,
        View $customerViewHelper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->persistentSession = $persistentSession;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getSectionData(): array
    {
        if (!$this->persistentSession->isPersistent()) {
            return [];
        }

        $customerId = $this->persistentSession->getSession()->getCustomerId();
        if (!$customerId) {
            return [];
        }

        $customer = $this->customerRepository->getById($customerId);

        return [
            'fullname' => $this->customerViewHelper->getCustomerName($customer),
        ];
    }
}
