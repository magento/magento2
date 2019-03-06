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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

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
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var array
     */
    private $restrictedKeys;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param CheckCustomerPassword $checkCustomerPassword
     * @param CustomerInterfaceFactory $customerFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $restrictedKeys
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        CheckCustomerPassword $checkCustomerPassword,
        CustomerInterfaceFactory $customerFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        array $restrictedKeys = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->customerFactory = $customerFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->restrictedKeys = $restrictedKeys;
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
        $newData = array_diff_key($data, array_flip($this->restrictedKeys));

        $oldData = $this->dataObjectProcessor->buildOutputDataArray($customer, CustomerInterface::class);
        $newData = array_merge($oldData, $newData);

        $customer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $newData,
            CustomerInterface::class
        );

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
