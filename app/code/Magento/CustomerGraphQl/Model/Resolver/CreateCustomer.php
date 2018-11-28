<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create customer data resolver
 */
class CreateCustomer implements ResolverInterface
{
    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerFactory
     * @param AccountManagementInterface $accountManagement
     * @param StoreManagerInterface $storeManager
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerDataProvider $customerDataProvider
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        StoreManagerInterface $storeManager,
        SubscriberFactory $subscriberFactory,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->customerDataProvider = $customerDataProvider;
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        try {
            $customer = $this->createUserAccount($args);
            $this->setUpUserContext($context, $customer);
            if (array_key_exists('is_subscribed', $args['input'])) {
                if ($args['input']['is_subscribed']) {
                    $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                }
            }
            $data = $this->customerDataProvider->getCustomerById((int)$customer->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return ['customer' => $data];
    }

    /**
     * @param $args
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createUserAccount($args)
    {
        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $args['input'],
            CustomerInterface::class
        );
        $store = $this->storeManager->getStore();
        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($store->getId());

        $password = array_key_exists('password', $args['input']) ? $args['input']['password'] : null;

        return $this->accountManagement->createAccount($customerDataObject, $password);
    }

    /**
     * @param $context
     * @param CustomerInterface $customer
     */
    private function setUpUserContext($context, $customer)
    {
        $context->setUserId((int)$customer->getId());
        $context->setUserType(UserContextInterface::USER_TYPE_CUSTOMER);
    }
}
