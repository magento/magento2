<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BraintreeTwo\Gateway\Command;

use Braintree\Result\Successful;
use Exception;
use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class GetPaymentNonceCommand
 */
class GetPaymentNonceCommand implements CommandInterface
{

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var ArrayResultFactory
     */
    private $resultFactory;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BraintreeAdapter $adapter
     * @param ArrayResultFactory $resultFactory
     */
    public function __construct(
        PaymentTokenManagementInterface $tokenManagement,
        BraintreeAdapter $adapter,
        ArrayResultFactory $resultFactory
    ) {
        $this->tokenManagement = $tokenManagement;
        $this->adapter = $adapter;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute(array $commandSubject)
    {
        $publicHash = $commandSubject['publicHash'];
        $customerId = $commandSubject['customerId'];
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if (!$paymentToken) {
            throw new Exception('No available payment tokens');
        }
        $result = $this->adapter->createNonce($paymentToken->getGatewayToken());
        if (!$result instanceof Successful || !$result->success) {
            throw new Exception('Payment nonce was not received');
        }
        return $this->resultFactory->create(['array' => ['paymentMethodNonce' => $result->paymentMethodNonce->nonce]]);
    }
}
