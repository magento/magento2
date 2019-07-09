<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Update customer account data
 */
class UpdateCustomerAccount
{
    /**
     * @var SaveCustomer
     */
    private $saveCustomer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ChangeSubscriptionStatus
     */
    private $changeSubscriptionStatus;

    /**
     * @var ValidateCustomerData
     */
    private $validateCustomerData;

    /**
     * @var array
     */
    private $restrictedKeys;

    /**
     * @param SaveCustomer $saveCustomer
     * @param StoreManagerInterface $storeManager
     * @param CheckCustomerPassword $checkCustomerPassword
     * @param DataObjectHelper $dataObjectHelper
     * @param ChangeSubscriptionStatus $changeSubscriptionStatus
     * @param ValidateCustomerData $validateCustomerData
     * @param array $restrictedKeys
     */
    public function __construct(
        SaveCustomer $saveCustomer,
        StoreManagerInterface $storeManager,
        CheckCustomerPassword $checkCustomerPassword,
        DataObjectHelper $dataObjectHelper,
        ChangeSubscriptionStatus $changeSubscriptionStatus,
        ValidateCustomerData $validateCustomerData,
        array $restrictedKeys = []
    ) {
        $this->saveCustomer = $saveCustomer;
        $this->storeManager = $storeManager;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->restrictedKeys = $restrictedKeys;
        $this->changeSubscriptionStatus = $changeSubscriptionStatus;
        $this->validateCustomerData = $validateCustomerData;
    }

    /**
     * Update customer account
     *
     * @param CustomerInterface $customer
     * @param array $data
     * @throws GraphQlAlreadyExistsException
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(CustomerInterface $customer, array $data): void
    {
        if (isset($data['email']) && $customer->getEmail() !== $data['email']) {
            if (!isset($data['password']) || empty($data['password'])) {
                throw new GraphQlInputException(__('Provide the current "password" to change "email".'));
            }

            $this->checkCustomerPassword->execute($data['password'], (int)$customer->getId());
            $customer->setEmail($data['email']);
        }
        $this->validateCustomerData->execute($data);
        $filteredData = array_diff_key($data, array_flip($this->restrictedKeys));
        $this->dataObjectHelper->populateWithArray($customer, $filteredData, CustomerInterface::class);

        try {
            $customer->setStoreId($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()), $exception);
        }

        $this->saveCustomer->execute($customer);

        if (isset($data['is_subscribed'])) {
            $this->changeSubscriptionStatus->execute((int)$customer->getId(), (bool)$data['is_subscribed']);
        }
    }
}
