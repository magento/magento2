<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\UserContextParametersProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddUserInfoToContext implements UserContextParametersProcessorInterface, ResetAfterRequestInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContextFromConstructor;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param UserContextInterface $userContext
     * @param Session $session
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        Session $session,
        CustomerRepository $customerRepository
    ) {
        $this->userContext = $userContext;
        $this->userContextFromConstructor = $userContext;
        $this->session = $session;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->userContext = $this->userContextFromConstructor;
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
            $customer = $this->customerRepository->getById($currentUserId);
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
        }
        return $contextParameters;
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
