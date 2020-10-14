<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as ResourceCompareList;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Service class for customer
 */
class CustomerService
{
    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @param AuthenticationInterface $authentication
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param ResourceCompareList  $resourceCompareList
     * @param CompareListFactory   $compareListFactory
     */
    public function __construct(
        AuthenticationInterface $authentication,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        ResourceCompareList $resourceCompareList,
        CompareListFactory $compareListFactory
    ) {
        $this->authentication = $authentication;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        $this->resourceCompareList = $resourceCompareList;
        $this->compareListFactory = $compareListFactory;
    }

    /**
     * Get listId by Customer ID
     *
     * @param $customerId
     *
     * @return int|null
     */
    public function getListIdByCustomerId($customerId)
    {
        if ($customerId) {
            /** @var CompareList $compareListModel */
            $compareListModel = $this->compareListFactory->create();
            $this->resourceCompareList->load($compareListModel, $customerId, 'customer_id');
            return (int)$compareListModel->getListId();
        }

        return null;
    }

    /**
     * Customer validate
     *
     * @param $customerId
     *
     * @return CustomerInterface
     *
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function validateCustomer($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Customer with id "%customer_id" does not exist.', ['customer_id' => $customerId]),
                $e
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        if (true === $this->authentication->isLocked($customerId)) {
            throw new GraphQlAuthenticationException(__('The account is locked.'));
        }

        try {
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customerId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
            throw new GraphQlAuthenticationException(__("This account isn't confirmed. Verify and try again."));
        }

        return $customer;
    }
}
