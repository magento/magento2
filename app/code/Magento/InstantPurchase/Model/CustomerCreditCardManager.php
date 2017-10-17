<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Exception;
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
     * @param string $customerId
     * @param string $cardId
     * @return mixed
     * @throws LocalizedException
     */
    public function getCustomerCreditCard(string $customerId, string $cardId)
    {
        $tokens = $this->getVisibleAvailableTokens($customerId);
        if (empty($tokens) || !$cardId || !isset($tokens[$cardId])) {
            throw new LocalizedException(__('There are no credit cards available.'));
        }

        return $tokens[$cardId];
    }

    /**
     * @param string $customerId
     * @return array
     */
    public function getVisibleAvailableTokens(string $customerId): array
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
     * @param string $customerId
     * @param string $publicHash
     * @return array
     */
    public function getPaymentAdditionalInformation(string $customerId, string $publicHash): array
    {
        return [
            'customer_id' => $customerId,
            'public_hash' => $publicHash,
            'payment_method_nonce' => $this->getNonce($publicHash, $customerId),
            'is_active_payment_token_enabler' => true
        ];
    }

    /**
     * @param string $field
     * @param $value
     * @return array
     */
    private function getFilter(string $field, $value): array
    {
        return [
            $this->filterBuilder->setField($field)
                ->setValue($value)
                ->create()
        ];
    }

    /**
     * @param string $publicHash
     * @param string $customerId
     * @return string
     * @throws Exception
     */
    private function getNonce(string $publicHash, string $customerId): string
    {
        return $this->getNonce->execute(
            ['public_hash' => $publicHash, 'customer_id' => $customerId]
        )->get()['paymentMethodNonce'];
    }
}
