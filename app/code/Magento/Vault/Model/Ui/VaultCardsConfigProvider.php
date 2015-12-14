<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProvider
 */
final class VaultCardsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var VaultPaymentInterface
     */
    private $vaultPayment;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Session $customerSession
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Session $customerSession,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        VaultPaymentInterface $vaultPayment
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
        $this->vaultPayment = $vaultPayment;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $vaultPayments = [];

        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return $vaultPayments;
        }

        if (!$this->vaultPayment->isActive($this->storeManager->getStore()->getId())) {
            return $vaultPayments;
        }

        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::IS_VISIBLE)
            ->setValue(true)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        $index = 1;
        foreach ($this->paymentTokenRepository->getList($searchCriteria)->getItems() as $token) {
            $vaultPayments[$this->vaultPayment->getCode() . '-' . $index] = [
                'token' => $token->getPublicHash(),
                'title' => __('Vault token - ' . $index)
            ];

            ++$index;
        }

        return [
            'payment' => [
                VaultPaymentInterface::CODE => $vaultPayments
            ]
        ];
    }
}
