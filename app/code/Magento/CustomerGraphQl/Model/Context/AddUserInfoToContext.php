<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\UserContextParametersProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddUserInfoToContext implements UserContextParametersProcessorInterface
{
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    private CustomerInterfaceFactory $customerFactory;

    /**
     * @param UserContextInterface $userContext
     * @param Session $session
     * @param CustomerRegistry $customerRegistry
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        Session $session,
        CustomerRegistry $customerRegistry,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->userContext = $userContext;
        $this->session = $session;
        $this->customerRegistry = $customerRegistry;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function setUserContext(UserContextInterface $userContext): void
    {
        $this->userContext = $userContext;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $currentUserId = $this->userContext->getUserId();
        if (null !== $currentUserId) {
            $currentUserId = (int)$currentUserId;
        }
        $contextParameters->setUserId($currentUserId);

        $currentUserType = $this->userContext->getUserType();
        if (null !== $currentUserType) {
            $currentUserType = (int)$currentUserType;
        }
        $contextParameters->setUserType($currentUserType);

        $isCustomer = $this->isCustomer($currentUserId, $currentUserType);
        $contextParameters->addExtensionAttribute('is_customer', $isCustomer);

        if ($isCustomer) {
            $customer = $this->customerRegistry->retrieve($currentUserId);
            $this->session->setCustomerData($this->getCustomerDataObject($customer));
            $this->session->setCustomerGroupId($customer->getGroupId());
        }
        return $contextParameters;
    }

    /**
     * Convert custom model to DTO
     *
     * @param Customer $customerModel
     * @return CustomerInterface
     */
    private function getCustomerDataObject(Customer $customerModel): CustomerInterface
    {
        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerModel->getData(),
            CustomerInterface::class
        );
        $customerDataObject->setId($customerModel->getId());
        return $customerDataObject;
    }

    /**
     * Get logged in customer data
     *
     * @return CustomerInterface
     */
    public function getLoggedInCustomerData(): ?CustomerInterface
    {
        return $this->session->isLoggedIn() ? $this->session->getCustomerData() : null;
    }

    /**
     * Checking if current user is logged
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isCustomer(?int $customerId, ?int $customerType): bool
    {
        return !empty($customerId)
            && !empty($customerType)
            && $customerType === UserContextInterface::USER_TYPE_CUSTOMER;
    }
}
