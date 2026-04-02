<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Model\ProductRenderList;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Context;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set customer group context for REST API pricing
 */
class ProductRenderListPlugin
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
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array Cache for customer group IDs
     */
    private $customerGroupCache = [];

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param HttpContext $httpContext
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        HttpContext $httpContext,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->httpContext = $httpContext;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * Set customer group context in HTTP context for REST API requests
     *
     * @param ProductRenderList $subject
     * @param SearchCriteriaInterface $searchCriteria
     * @param int|null $storeId
     * @param string|null $currencyCode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetList(
        ProductRenderList $subject,
        SearchCriteriaInterface $searchCriteria,
        $storeId = null,
        $currencyCode = null
    ): array {
        try {
            $areaCode = $this->appState->getAreaCode();
            if (!in_array($areaCode, ['webapi_rest', 'webapi_soap'], true)) {
                return [$searchCriteria, $storeId, $currencyCode];
            }
            $customerGroupId = $this->getCustomerGroupId();

            if ($customerGroupId !== null) {
                // Set in HTTP context for cache and general context
                $this->httpContext->setValue(
                    Context::CONTEXT_GROUP,
                    (string)$customerGroupId,
                    Group::NOT_LOGGED_IN_ID
                );
            }

        } catch (\Exception $e) {
            $this->logger->error(
                'Error in ProductRenderList plugin: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }

        return [$searchCriteria, $storeId, $currencyCode];
    }

    /**
     * Get customer group ID from authenticated user context
     *
     * @return int|null
     */
    private function getCustomerGroupId(): ?int
    {
        try {
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
            // For guest users, return the not logged in group ID
            return Group::NOT_LOGGED_IN_ID;

        } catch (NoSuchEntityException $e) {
            return Group::NOT_LOGGED_IN_ID;
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
