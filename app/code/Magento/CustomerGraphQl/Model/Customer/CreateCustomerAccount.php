<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Create new customer account
 */
class CreateCustomerAccount
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var ValidateCustomerData
     */
    private $validateCustomerData;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * CreateCustomerAccount constructor.
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerFactory
     * @param AccountManagementInterface $accountManagement
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ValidateCustomerData $validateCustomerData
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        DataObjectProcessor $dataObjectProcessor,
        ValidateCustomerData $validateCustomerData,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->validateCustomerData = $validateCustomerData;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Creates new customer account
     *
     * @param array $data
     * @param StoreInterface $store
     * @return CustomerInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data, StoreInterface $store): CustomerInterface
    {
        try {
            $customer = $this->createAccount($data, $store);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        if (isset($data['is_subscribed'])) {
            if ((bool)$data['is_subscribed']) {
                $this->subscriptionManager->subscribeCustomer((int)$customer->getId(), (int)$store->getId());
            } else {
                $this->subscriptionManager->unsubscribeCustomer((int)$customer->getId(), (int)$store->getId());
            }
        }
        return $customer;
    }

    /**
     * Create account
     *
     * @param array $data
     * @param StoreInterface $store
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function createAccount(array $data, StoreInterface $store): CustomerInterface
    {
        $customerDataObject = $this->customerFactory->create();
        /**
         * Add required attributes for customer entity
         */
        $requiredDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $customerDataObject,
            CustomerInterface::class
        );
        $data = array_merge($requiredDataAttributes, $data);
        $this->validateCustomerData->execute($data);
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $data,
            CustomerInterface::class
        );
        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($store->getId());

        $password = array_key_exists('password', $data) ? $data['password'] : null;
        return $this->accountManagement->createAccount($customerDataObject, $password);
    }
}
