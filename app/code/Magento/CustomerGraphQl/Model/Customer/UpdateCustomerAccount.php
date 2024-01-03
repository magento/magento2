<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Update customer account data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) - https://jira.corp.magento.com/browse/MC-18152
 */
class UpdateCustomerAccount
{
    /**
     * @var SaveCustomer
     */
    private $saveCustomer;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ValidateCustomerData
     */
    private $validateCustomerData;

    /**
     * @var array
     */
    private $restrictedKeys;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @param SaveCustomer $saveCustomer
     * @param CheckCustomerPassword $checkCustomerPassword
     * @param DataObjectHelper $dataObjectHelper
     * @param ValidateCustomerData $validateCustomerData
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param array $restrictedKeys
     */
    public function __construct(
        SaveCustomer $saveCustomer,
        CheckCustomerPassword $checkCustomerPassword,
        DataObjectHelper $dataObjectHelper,
        ValidateCustomerData $validateCustomerData,
        SubscriptionManagerInterface $subscriptionManager,
        array $restrictedKeys = []
    ) {
        $this->saveCustomer = $saveCustomer;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->restrictedKeys = $restrictedKeys;
        $this->validateCustomerData = $validateCustomerData;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Update customer account
     *
     * @param CustomerInterface $customer
     * @param array $data
     * @param StoreInterface $store
     * @return void
     * @throws GraphQlAlreadyExistsException
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(CustomerInterface $customer, array $data, StoreInterface $store): void
    {
        if (isset($data['email']) && $customer->getEmail() !== $data['email']) {
            if (empty($data['password'])) {
                throw new GraphQlInputException(__('Provide the current "password" to change "email".'));
            }

            $this->checkCustomerPassword->execute($data['password'], (int)$customer->getId());
            $customer->setEmail($data['email']);
        }

        $this->validateCustomerData->execute($data);
        $filteredData = array_diff_key($data, array_flip($this->restrictedKeys));
        $this->dataObjectHelper->populateWithArray($customer, $filteredData, CustomerInterface::class);

        try {
            $customer->setStoreId($store->getId());
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()), $exception);
        }

        $this->saveCustomer->execute($customer);

        if (isset($data['is_subscribed'])) {
            if ((bool)$data['is_subscribed']) {
                $this->subscriptionManager->subscribeCustomer((int)$customer->getId(), (int)$store->getId());
            } else {
                $this->subscriptionManager->unsubscribeCustomer((int)$customer->getId(), (int)$store->getId());
            }
        }
    }
}
