<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Plugin\Model\Group;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Group\Retriever;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Plugin to override customer group retrieval for API requests
 */
class RetrieverPlugin
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var array Cache for customer group IDs
     */
    private $customerGroupCache = [];

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param State $appState
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        State $appState
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->appState = $appState;
    }

    /**
     * Override customer group retrieval for API requests
     *
     * @param Retriever $subject
     * @param \Closure $proceed
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCustomerGroupId(
        Retriever $subject,
        \Closure $proceed
    ): int {
        try {
            $areaCode = $this->appState->getAreaCode();
            if (!in_array($areaCode, ['webapi_rest', 'webapi_soap'], true)) {
                return $proceed();
            }

            $userType = $this->userContext->getUserType();
            if ($userType === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();
                if ($customerId) {
                    if (!isset($this->customerGroupCache[$customerId])) {
                        $customer = $this->customerRepository->getById($customerId);
                        $this->customerGroupCache[$customerId] = (int)$customer->getGroupId();
                    }
                    return $this->customerGroupCache[$customerId];
                }
            }
            return Group::NOT_LOGGED_IN_ID;

        } catch (NoSuchEntityException $e) {
            return Group::NOT_LOGGED_IN_ID;
        } catch (LocalizedException $e) {
            return $proceed();
        }
    }
}
