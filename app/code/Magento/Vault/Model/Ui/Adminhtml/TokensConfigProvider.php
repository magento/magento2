<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui\Adminhtml;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProvider
 * @api
 */
final class TokensConfigProvider
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
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var VaultPaymentInterface
     */
    private $vaultPayment;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TokenUiComponentProviderInterface[]
     */
    private $tokenUiComponentProviders;

    /**
     * Constructor
     *
     * @param SessionManagerInterface $session
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param VaultPaymentInterface $vaultPayment
     * @param TokenUiComponentProviderInterface[] $tokenUiComponentProviders
     */
    public function __construct(
        SessionManagerInterface $session,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        VaultPaymentInterface $vaultPayment,
        array $tokenUiComponentProviders = []
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->session = $session;
        $this->vaultPayment = $vaultPayment;
        $this->storeManager = $storeManager;
        $this->tokenUiComponentProviders = $tokenUiComponentProviders;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $vaultPayments = [];

        $customerId = $this->session->getCustomerId();
        if (!$customerId) {
            return $vaultPayments;
        }

        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->vaultPayment->isActive($storeId)) {
            return $vaultPayments;
        }

        $vaultProviderCode = $this->vaultPayment->getProviderCode($storeId);
        $componentProvider = $this->getComponentProvider($vaultProviderCode);
        if (null === $componentProvider) {
            return $vaultPayments;
        }

        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::PAYMENT_METHOD_CODE)
            ->setValue($vaultProviderCode)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        foreach ($this->paymentTokenRepository->getList($searchCriteria)->getItems() as $index => $token) {
            $component = $componentProvider->getComponentForToken($token);
            $vaultPayments[VaultPaymentInterface::CODE . $index] = [
                'config' => $component->getConfig(),
                'component' => $component->getName()
            ];
        }

        return $vaultPayments;
    }

    /**
     * @param string $vaultProviderCode
     * @return TokenUiComponentProviderInterface|null
     */
    private function getComponentProvider($vaultProviderCode)
    {
        $componentProvider = isset($this->tokenUiComponentProviders[$vaultProviderCode])
            ? $this->tokenUiComponentProviders[$vaultProviderCode]
            : null;
        return $componentProvider instanceof TokenUiComponentProviderInterface
            ? $componentProvider
            : null;
    }
}
