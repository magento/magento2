<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Delete customer by email or id
 */
class DeleteCustomer
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Registry */
    private $registry;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Registry $registry
     */
    public function __construct(CustomerRepositoryInterface $customerRepository, Registry $registry)
    {
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
    }

    /**
     * Delete customer by id or email
     *
     * @param int|string $id
     * @return void
     */
    public function execute($id): void
    {
        $isSecure = $this->registry->registry('isSecureArea');

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        try {
            $customer = is_numeric($id) ? $this->customerRepository->getById($id) : $this->customerRepository->get($id);
            $this->customerRepository->delete($customer);
        } catch (NoSuchEntityException $e) {
            //customer already deleted
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', $isSecure);
    }
}
