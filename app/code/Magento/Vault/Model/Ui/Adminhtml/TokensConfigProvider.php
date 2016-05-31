<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui\Adminhtml;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProvider
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var string
     */
    private $providerCode;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * Constructor
     *
     * @param SessionManagerInterface $session
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param VaultPaymentInterface $vaultPayment
     * @param DateTimeFactory $dateTimeFactory
     * @param TokenUiComponentProviderInterface[] $tokenUiComponentProviders
     */
    public function __construct(
        SessionManagerInterface $session,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        VaultPaymentInterface $vaultPayment,
        DateTimeFactory $dateTimeFactory,
        array $tokenUiComponentProviders = []
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->session = $session;
        $this->vaultPayment = $vaultPayment;
        $this->storeManager = $storeManager;
        $this->tokenUiComponentProviders = $tokenUiComponentProviders;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @return TokenUiComponentInterface[]
     */
    public function getTokensComponents()
    {
        $result = [];

        $customerId = $this->session->getCustomerId();
        if (!$customerId) {
            return $result;
        }

        $vaultProviderCode = $this->getProviderMethodCode();
        $componentProvider = $this->getComponentProvider($vaultProviderCode);
        if (null === $componentProvider) {
            return $result;
        }

        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::PAYMENT_METHOD_CODE)
            ->setValue($vaultProviderCode)
            ->create();
        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::IS_ACTIVE)
            ->setValue(1)
            ->create();
        $filters[] = $this->filterBuilder->setField(PaymentTokenInterface::EXPIRES_AT)
            ->setConditionType('gt')
            ->setValue(
                $this->dateTimeFactory->create(
                    'now',
                    new \DateTimeZone('UTC')
                )->format('Y-m-d 00:00:00')
            )
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        foreach ($this->paymentTokenRepository->getList($searchCriteria)->getItems() as $token) {
            $result[] = $componentProvider->getComponentForToken($token);
        }

        return $result;
    }

    /**
     * Get code of payment method provider
     * @return null|string
     */
    private function getProviderMethodCode()
    {
        if (!$this->providerCode) {
            $storeId = $this->getStoreId();
            $this->providerCode = $storeId ? $this->vaultPayment->getProviderCode($storeId) : null;
        }
        return $this->providerCode;
    }

    /**
     * Get store id for current active vault payment
     * @return int|null
     */
    private function getStoreId()
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->vaultPayment->isActive($storeId)) {
            return null;
        }
        return $storeId;
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
