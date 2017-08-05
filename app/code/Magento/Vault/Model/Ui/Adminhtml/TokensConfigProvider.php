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
 * @since 100.1.0
 */
class TokensConfigProvider
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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentTokenManagementInterface
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
     * @since 100.1.0
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
     * @since 100.1.0
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
     * @deprecated 100.1.0 100.1.0 2.1.0
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
     * @deprecated 100.2.0 100.2.0 2.1.3
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
     * @deprecated 100.2.0 100.2.0 2.1.3
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
