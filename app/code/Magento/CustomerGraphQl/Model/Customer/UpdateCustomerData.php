<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Update customer data
 */
class UpdateCustomerData
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param CheckCustomerPassword $checkCustomerPassword
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        CheckCustomerPassword $checkCustomerPassword
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->checkCustomerPassword = $checkCustomerPassword;
    }

    /**
     * Update account information
     *
     * @param int $customerId
     * @param array $data
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws GraphQlAlreadyExistsException
     */
    public function execute(int $customerId, array $data): void
    {
        $customer = $this->customerRepository->getById($customerId);

        if (isset($data['firstname'])) {
            $customer->setFirstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $customer->setLastname($data['lastname']);
        }

        if (isset($data['email']) && $customer->getEmail() !== $data['email']) {
            if (!isset($data['password']) || empty($data['password'])) {
                throw new GraphQlInputException(__('Provide the current "password" to change "email".'));
            }

            $this->checkCustomerPassword->execute($data['password'], $customerId);
            $customer->setEmail($data['email']);
        }

        $customer->setStoreId($this->storeManager->getStore()->getId());

        try {
            $this->customerRepository->save($customer);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(
                __('A customer with the same email address already exists in an associated website.'),
                $e
            );
        }
    }
}
