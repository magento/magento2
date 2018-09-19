<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\PaymentMethodChoose;

use Magento\Customer\Model\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\InstantPurchase\PaymentMethodIntegration\Integration;
use Magento\InstantPurchase\PaymentMethodIntegration\IntegrationsManager;
use Magento\Store\Model\Store;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Payment token chooser  to select most recent token.
 *
 * Current im
 */
class LastCreatedPaymentTokenChooser implements PaymentTokenChooserInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var IntegrationsManager
     */
    private $integrationsManager;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * LastCreatedPaymentTokenChooser constructor.
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param IntegrationsManager $integrationsManager
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTimeFactory $dateTimeFactory]
     */
    public function __construct(
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        IntegrationsManager $integrationsManager,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->integrationsManager = $integrationsManager;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritdoc
     */
    public function choose(Store $store, Customer $customer)
    {
        $searchCriteria = $this->buildSearchCriteria($store->getId(), $customer->getId());
        $searchResult = $this->paymentTokenRepository->getList($searchCriteria);
        $tokens = $searchResult->getItems();
        $lastCreatedToken = array_shift($tokens);
        return $lastCreatedToken;
    }

    /**
     * Builds search criteria to find available payment tokens
     *
     * @param int $storeId
     * @param int $customerId
     * @return SearchCriteriaInterface
     */
    private function buildSearchCriteria(int $storeId, int $customerId): SearchCriteriaInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::CUSTOMER_ID,
            $customerId
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::IS_VISIBLE,
            1
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::IS_ACTIVE,
            1
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            $this->getSupportedPaymentMethodCodes($storeId),
            'in'
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::EXPIRES_AT,
            $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'))
                ->format('Y-m-d 00:00:00'),
            'gt'
        );

        $creationReverseOrder = $this->sortOrderBuilder->setField(PaymentTokenInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($creationReverseOrder);
        $this->searchCriteriaBuilder->setPageSize(1);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $searchCriteria;
    }

    /**
     * Lists supported payment method codes.
     *
     * @param int $storeId
     * @return array
     */
    private function getSupportedPaymentMethodCodes(int $storeId)
    {
        $integrations = $this->integrationsManager->getList($storeId);
        $integrations = array_filter($integrations, function (Integration $integration) {
            return $integration->isAvailable();
        });
        $paymentMethodCodes = array_map(function (Integration $integration) {
            return $integration->getVaultProviderCode();
        }, $integrations);
        return $paymentMethodCodes;
    }
}
