<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui\Adminhtml;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Helper\Data;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TokenUiComponentProviderInterface[]
     */
    private $tokenUiComponentProviders;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Data
     */
    private $paymentDataHelper;

    /**
     * Constructor
     *
     * @param SessionManagerInterface $session
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param DateTimeFactory $dateTimeFactory
     * @param TokenUiComponentProviderInterface[] $tokenUiComponentProviders
     */
    public function __construct(
        SessionManagerInterface $session,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        DateTimeFactory $dateTimeFactory,
        array $tokenUiComponentProviders = []
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->tokenUiComponentProviders = $tokenUiComponentProviders;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param string $vaultPaymentCode
     * @return TokenUiComponentInterface[]
     */
    public function getTokensComponents($vaultPaymentCode)
    {
        $result = [];

        $customerId = $this->session->getCustomerId();
        if (!$customerId) {
            return $result;
        }

        $vaultPayment = $this->getVaultPayment($vaultPaymentCode);
        if ($vaultPayment === null) {
            return $result;
        }

        $vaultProviderCode = $vaultPayment->getProviderCode();
        $componentProvider = $this->getComponentProvider($vaultProviderCode);
        if ($componentProvider === null) {
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

    /**
     * Get active vault payment by code
     * @param $vaultPaymentCode
     * @return VaultPaymentInterface|null
     */
    private function getVaultPayment($vaultPaymentCode)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPayment = $this->getPaymentDataHelper()->getMethodInstance($vaultPaymentCode);
        return $vaultPayment->isActive($storeId) ? $vaultPayment : null;
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}
