<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui\Adminhtml;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProvider
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.1.0
 */
class TokensConfigProvider
{
    /**
     * @var PaymentTokenRepositoryInterface
     * @since 2.1.0
     */
    private $paymentTokenRepository;

    /**
     * @var FilterBuilder
     * @since 2.1.0
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var SessionManagerInterface
     * @since 2.1.0
     */
    private $session;

    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    private $storeManager;

    /**
     * @var TokenUiComponentProviderInterface[]
     * @since 2.1.0
     */
    private $tokenUiComponentProviders;

    /**
     * @var DateTimeFactory
     * @since 2.1.0
     */
    private $dateTimeFactory;

    /**
     * @var Data
     * @since 2.1.0
     */
    private $paymentDataHelper;

    /**
     * @var OrderRepositoryInterface
     * @since 2.1.3
     */
    private $orderRepository;

    /**
     * @var PaymentTokenManagementInterface
     * @since 2.1.3
     */
    private $paymentTokenManagement;

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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getTokensComponents($vaultPaymentCode)
    {
        $result = [];

        $customerId = $this->session->getCustomerId();

        $vaultPayment = $this->getVaultPayment($vaultPaymentCode);
        if ($vaultPayment === null) {
            return $result;
        }

        $vaultProviderCode = $vaultPayment->getProviderCode();
        $componentProvider = $this->getComponentProvider($vaultProviderCode);
        if ($componentProvider === null) {
            return $result;
        }

        if ($customerId) {
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
                    ->setValue($customerId)
                    ->create(),
                ]
            );
        } else {
            try {
                $this->searchCriteriaBuilder->addFilters(
                    [
                        $this->filterBuilder->setField(PaymentTokenInterface::ENTITY_ID)
                            ->setValue($this->getPaymentTokenEntityId())
                            ->create(),
                    ]
                );
            } catch (InputException $e) {
                return $result;
            } catch (NoSuchEntityException $e) {
                return $result;
            }
        }
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder->setField(PaymentTokenInterface::PAYMENT_METHOD_CODE)
                    ->setValue($vaultProviderCode)
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

        $searchCriteria = $this->searchCriteriaBuilder->create();

        foreach ($this->paymentTokenRepository->getList($searchCriteria)->getItems() as $token) {
            $result[] = $componentProvider->getComponentForToken($token);
        }

        return $result;
    }

    /**
     * @param string $vaultProviderCode
     * @return TokenUiComponentProviderInterface|null
     * @since 2.1.0
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
     * @param string $vaultPaymentCode
     * @return VaultPaymentInterface|null
     * @since 2.1.0
     */
    private function getVaultPayment($vaultPaymentCode)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPayment = $this->getPaymentDataHelper()->getMethodInstance($vaultPaymentCode);
        return $vaultPayment->isActive($storeId) ? $vaultPayment : null;
    }

    /**
     * Returns payment token entity id by order payment id
     * @return int|null
     * @since 2.1.3
     */
    private function getPaymentTokenEntityId()
    {
        $paymentToken = $this->getPaymentTokenManagement()->getByPaymentId($this->getOrderPaymentEntityId());
        if ($paymentToken === null) {
            throw new NoSuchEntityException(__('No available payment tokens for specified order payment.'));
        }
        return $paymentToken->getEntityId();
    }

    /**
     * Returns order payment entity id
     * Using 'getReordered' for Reorder action
     * Using 'getOrder' for Edit action
     * @return int
     * @since 2.1.3
     */
    private function getOrderPaymentEntityId()
    {
        $orderId = $this->session->getReordered()
            ?: $this->session->getOrder()->getEntityId();
        $order = $this->getOrderRepository()->get($orderId);

        return (int) $order->getPayment()->getEntityId();
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }

    /**
     * Returns order repository instance
     * @return OrderRepositoryInterface
     * @deprecated 2.1.3
     * @since 2.1.3
     */
    private function getOrderRepository()
    {
        if ($this->orderRepository === null) {
            $this->orderRepository = ObjectManager::getInstance()
                ->get(OrderRepositoryInterface::class);
        }

        return $this->orderRepository;
    }

    /**
     * Returns payment token management instance
     * @return PaymentTokenManagementInterface
     * @deprecated 2.1.3
     * @since 2.1.3
     */
    private function getPaymentTokenManagement()
    {
        if ($this->paymentTokenManagement === null) {
            $this->paymentTokenManagement = ObjectManager::getInstance()
                ->get(PaymentTokenManagementInterface::class);
        }

        return $this->paymentTokenManagement;
    }
}
