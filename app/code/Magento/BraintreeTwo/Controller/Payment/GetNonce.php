<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Controller\Payment;

use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Psr\Log\LoggerInterface;
use Braintree\Result\Successful;

/**
 * Class GetNonce
 */
class GetNonce extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Session $session
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BraintreeAdapter $adapter
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Session $session,
        PaymentTokenManagementInterface $tokenManagement,
        BraintreeAdapter $adapter
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->session = $session;
        $this->tokenManagement = $tokenManagement;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $publicHash = $this->getRequest()->getParam('public_hash');
            if (!$publicHash) {
                return $this->processInvalidParamRequest($response);
            }

            $customerId = $this->session->getCustomerId();
            if (!$customerId) {
                return $this->processInvalidCustomer($response);
            }
            $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
            if (!$paymentToken) {
                return $this->processNotFoundPaymentToken($response);
            }
            $result = $this->adapter->createNonce($paymentToken->getGatewayToken());
            if (!$result instanceof Successful || !$result->success) {
                return $this->processBadRequest($response);
            }
            $response->setData(['paymentMethodNonce' => $result->paymentMethodNonce->nonce]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->processBadRequest($response);
        }

        return $response;
    }

    /**
     * Return response for invalid publish hash param
     * @param ResultInterface $response
     * @return ResultInterface
     */
    private function processInvalidParamRequest(ResultInterface $response)
    {
        $response->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $response->setData(['message' => __('The "public_hash" should not be empty')]);

        return $response;
    }

    /**
     * Return response for invalid customer session
     * @param ResultInterface $response
     * @return ResultInterface
     */
    private function processInvalidCustomer(ResultInterface $response)
    {
        $response->setHttpResponseCode(Exception::HTTP_UNAUTHORIZED);
        $response->setData(['message' => __('You are not allowed to perform this action')]);

        return $response;
    }

    /**
     * Return response for not found payment token
     * @param ResultInterface $response
     * @return ResultInterface
     */
    private function processNotFoundPaymentToken(ResultInterface $response)
    {
        $response->setHttpResponseCode(Exception::HTTP_NOT_FOUND);
        $response->setData(['message' => __('No available payment tokens')]);

        return $response;
    }

    /**
     * Return response for bad request
     * @param ResultInterface $response
     * @return ResultInterface
     */
    private function processBadRequest(ResultInterface $response)
    {
        $response->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $response->setData(['message' => __('Sorry, but something went wrong')]);

        return $response;
    }
}
