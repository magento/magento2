<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;

/**
 * Vault payment token repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenManagement implements PaymentTokenManagementInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * @var PaymentTokenResourceModel
     */
    protected $paymentTokenResourceModel;

    /**
     * @var PaymentTokenResourceModel
     */
    protected $resourceModel;

    /**
     * @var PaymentTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param PaymentTokenRepositoryInterface $repository
     * @param PaymentTokenResourceModel $paymentTokenResourceModel
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param EncryptorInterface $encryptor
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        PaymentTokenRepositoryInterface $repository,
        PaymentTokenResourceModel $paymentTokenResourceModel,
        PaymentTokenFactory $paymentTokenFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory,
        EncryptorInterface $encryptor,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->paymentTokenRepository = $repository;
        $this->paymentTokenResourceModel = $paymentTokenResourceModel;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->encryptor = $encryptor;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param int $customerId Customer ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface[] Payment tokens search result interface.
     */
    public function getListByCustomerId($customerId)
    {
        $filters[] = $this->filterBuilder
            ->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
        $entities = $this->paymentTokenRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilters($filters)
                ->create()
        )->getItems();

        return $entities;
    }

    /**
     * Searches for all visible, non-expired tokens
     *
     * @param int $customerId
     * @return Data\PaymentTokenInterface[]
     */
    public function getVisibleAvailableTokens($customerId)
    {
        $customerFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::CUSTOMER_ID)
                ->setValue($customerId)
                ->create()
            ];
        $visibleFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::IS_VISIBLE)
                ->setValue(1)
                ->create()
            ];
        $isActiveFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::IS_ACTIVE)
                ->setValue(1)
                ->create()
            ];
        $expiresAtFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::EXPIRES_AT)
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
        // add filters to different filter groups in order to filter by AND expression
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($expiresAtFilter)->create();

        return $this->paymentTokenRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get payment token by token Id.
     *
     * @param int $paymentId The payment token ID.
     * @return PaymentTokenInterface|null Payment token interface.
     */
    public function getByPaymentId($paymentId)
    {
        $tokenData = $this->paymentTokenResourceModel->getByOrderPaymentId($paymentId);
        $tokenModel = !empty($tokenData) ? $this->paymentTokenFactory->create(['data' => $tokenData]) : null;
        return $tokenModel;
    }

    /**
     * Get payment token by gateway token.
     *
     * @param string $token The gateway token.
     * @param string $paymentMethodCode
     * @param int $customerId Customer ID.
     * @return PaymentTokenInterface|null Payment token interface.
     */
    public function getByGatewayToken($token, $paymentMethodCode, $customerId)
    {
        $tokenData = $this->paymentTokenResourceModel->getByGatewayToken($token, $paymentMethodCode, $customerId);
        $tokenModel = !empty($tokenData) ? $this->paymentTokenFactory->create(['data' => $tokenData]) : null;
        return $tokenModel;
    }

    /**
     * Get payment token by public hash.
     *
     * @param string $hash Public hash.
     * @param int $customerId Customer ID.
     * @return PaymentTokenInterface|null Payment token interface.
     */
    public function getByPublicHash($hash, $customerId)
    {
        $tokenData = $this->paymentTokenResourceModel->getByPublicHash($hash, $customerId);
        $tokenModel = !empty($tokenData) ? $this->paymentTokenFactory->create(['data' => $tokenData]) : null;
        return $tokenModel;
    }

    /**
     * @param PaymentTokenInterface $token
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    public function saveTokenWithPaymentLink(PaymentTokenInterface $token, OrderPaymentInterface $payment)
    {
        $tokenDuplicate = $this->getByPublicHash(
            $token->getPublicHash(),
            $token->getCustomerId()
        );

        if (!empty($tokenDuplicate)) {
            if ($token->getIsVisible() || $tokenDuplicate->getIsVisible()) {
                $token->setEntityId($tokenDuplicate->getEntityId());
                $token->setIsVisible(true);
            } elseif ($token->getIsVisible() === $tokenDuplicate->getIsVisible()) {
                $token->setEntityId($tokenDuplicate->getEntityId());
            } else {
                $token->setPublicHash(
                    $this->encryptor->getHash(
                        $token->getPublicHash() . $token->getGatewayToken()
                    )
                );
            }
        }

        $this->paymentTokenRepository->save($token);

        $result = $this->addLinkToOrderPayment($token->getEntityId(), $payment->getEntityId());

        return $result;
    }

    /**
     * Add link between payment token and order payment.
     *
     * @param int $paymentTokenId Payment token ID.
     * @param int $orderPaymentId Order payment ID.
     * @return bool
     */
    public function addLinkToOrderPayment($paymentTokenId, $orderPaymentId)
    {
        return $this->paymentTokenResourceModel->addLinkToOrderPayment($paymentTokenId, $orderPaymentId);
    }
}
