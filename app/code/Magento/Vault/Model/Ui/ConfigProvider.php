<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'vault';

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
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $index = 1;
        $vaultPayments = [];
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return $vaultPayments;
        }

        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        foreach ($this->paymentTokenRepository->getList($searchCriteria)->getItems() as $token) {
            $vaultPayments[self::CODE . '-' . $index] = [
                'token' => $token->getPublicHash(),
                'title' => __('Vault token - ' . $index)
            ];

            ++$index;
        }

        return [
            'payment' => [
                self::CODE => $vaultPayments
            ]
        ];
    }
}
