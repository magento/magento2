<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
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
     * @param PaymentTokenRepositoryInterface $repository
     * @param PaymentTokenResourceModel $paymentTokenResourceModel
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        PaymentTokenRepositoryInterface $repository,
        PaymentTokenResourceModel $paymentTokenResourceModel,
        PaymentTokenFactory $paymentTokenFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->paymentTokenRepository = $repository;
        $this->paymentTokenResourceModel = $paymentTokenResourceModel;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
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
            $token->setEntityId($tokenDuplicate->getEntityId());
            $token->setIsVisible($token->getIsActive());
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
    protected function addLinkToOrderPayment($paymentTokenId, $orderPaymentId)
    {
        return $this->paymentTokenResourceModel->addLinkToOrderPayment($paymentTokenId, $orderPaymentId);
    }
}
