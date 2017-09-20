<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;

class CustomerCreditCardManager
{
    /**
     * @var \Magento\Vault\Api\PaymentTokenRepositoryInterface
     */
    private $repository;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var GetPaymentNonceCommand
     */
    private $getNonce;

    /**
     * CustomerCreditCardManager constructor.
     * @param \Magento\Vault\Api\PaymentTokenRepositoryInterface $repository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTimeFactory $dateTimeFactory
     * @param GetPaymentNonceCommand $getNonce
     */
    public function __construct(
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $repository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateTimeFactory,
        GetPaymentNonceCommand $getNonce
    ) {
        $this->repository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->getNonce = $getNonce;
    }

    /**
     * @param $customerId
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface
     * @throws LocalizedException
     */
    public function getCustomerCreditCard($customerId)
    {
        $tokens = $this->getVisibleAvailableTokens($customerId);
        if (empty($tokens)) {
            throw new LocalizedException(
                __('There are no credit cards available.')
            );
        }

        return array_shift($tokens);
    }

    /**
     * @param $publicHash
     * @param $customerId
     * @return string
     */
    public function getNonce($publicHash, $customerId): string
    {
        return $this->getNonce->execute(
            ['public_hash' => $publicHash, 'customer_id' => $customerId]
        )->get()['paymentMethodNonce'];
    }

    /**
     * @param $customerId
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface[]
     */
    public function getVisibleAvailableTokens($customerId): array
    {
        $customerFilter = $this->getFilter(\Magento\Vault\Api\Data\PaymentTokenInterface::CUSTOMER_ID, $customerId);
        $visibleFilter = $this->getFilter(\Magento\Vault\Api\Data\PaymentTokenInterface::IS_VISIBLE, 1);
        $isActiveFilter = $this->getFilter(\Magento\Vault\Api\Data\PaymentTokenInterface::IS_ACTIVE, 1);
        $isBrainTreeFilter = $this->getFilter(
            \Magento\Vault\Api\Data\PaymentTokenInterface::PAYMENT_METHOD_CODE,
            BrainTreeConfigProvider::CODE
        );

        $expiresAtFilter = [
            $this->filterBuilder->setField(\Magento\Vault\Api\Data\PaymentTokenInterface::EXPIRES_AT)
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
        $this->searchCriteriaBuilder->addFilters($isBrainTreeFilter);

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($expiresAtFilter)->create();

        return $this->repository->getList($searchCriteria)->getItems();
    }

    /**
     * @param $field
     * @param $value
     * @return array
     */
    private function getFilter($field, $value): array
    {
        return [
            $this->filterBuilder->setField($field)
                ->setValue($value)
                ->create()
        ];
    }
}
