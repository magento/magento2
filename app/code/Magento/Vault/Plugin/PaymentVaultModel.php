<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Plugin;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Method\Vault;

/**
 * Payment vault model process
 */
class PaymentVaultModel
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param DateTimeFactory $dateTimeFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        DateTimeFactory $dateTimeFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * Check Vault payment method is available
     *
     * @param Vault $subject
     * @param bool $result
     * @param CartInterface $quote
     * @return bool
     */
    public function afterIsAvailable(
        Vault $subject,
        $result,
        CartInterface $quote
    ) {
        $customerId = $quote->getCustomerId();
        if ($result && $customerId) {
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
                    ->setValue($customerId)
                    ->create(),
                ]
            );
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::PAYMENT_METHOD_CODE)
                        ->setValue($subject->getProviderCode())
                        ->create(),
                    ]
            );
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::IS_ACTIVE)
                        ->setValue(1)
                        ->create(),
                    ]
            );
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::EXPIRES_AT)
                        ->setConditionType('gt')
                        ->setValue(
                            $this->dateTimeFactory->create(
                                'now',
                                new \DateTimeZone('UTC')
                            )->format('Y-m-d 00:00:00')
                        )
                        ->create(),
                    ]
            );
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::IS_VISIBLE)
                        ->setValue(1)
                        ->create(),
                ]
            );
            
            $searchCriteria = $this->searchCriteriaBuilder->create();
            if (!$this->paymentTokenRepository->getList($searchCriteria)->getItems()) {
                return false;
            }
        }
        return $result;
    }
}
