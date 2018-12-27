<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Vault payment token repository
 */
class VisibleTokenRetriever
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param PaymentTokenRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        PaymentTokenRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->paymentTokenRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Searches for all visible, non-expired tokens
     *
     * @param int $customerId
     * @return PaymentTokenInterface[]
     */
    public function getVisibleAvailableTokens($customerId)
    {
        $customerFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
                ->setValue($customerId)
                ->create()
            ];
        $visibleFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::IS_VISIBLE)
                ->setValue(1)
                ->create()
            ];
        $isActiveFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::IS_ACTIVE)
                ->setValue(1)
                ->create()
            ];
        $expiresAtFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::EXPIRES_AT)
                ->setConditionType('gt')
                ->setValue(
                    $this->dateTimeFactory->create(
                        'now',
                        new \DateTimeZone('UTC')
                    )->format('Y-m-d 00:00:00')
                )
                ->create()
            ];
        $this->searchCriteriaBuilder->addFilters($customerFilter);
        $this->searchCriteriaBuilder->addFilters($visibleFilter);
        $this->searchCriteriaBuilder->addFilters($isActiveFilter);
        // add filters to different filter groups in order to filter by AND expression
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($expiresAtFilter)->create();

        return $this->paymentTokenRepository->getList($searchCriteria)->getItems();
    }
}
