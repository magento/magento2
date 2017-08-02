<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Gateway\Command;

use Exception;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class GetPaymentNonceCommand
 * @since 2.1.0
 */
class GetPaymentNonceCommand implements CommandInterface
{

    /**
     * @var PaymentTokenManagementInterface
     * @since 2.1.0
     */
    private $tokenManagement;

    /**
     * @var BraintreeAdapter
     * @since 2.1.0
     */
    private $adapter;

    /**
     * @var ArrayResultFactory
     * @since 2.1.0
     */
    private $resultFactory;

    /**
     * @var SubjectReader
     * @since 2.1.0
     */
    private $subjectReader;

    /**
     * @var PaymentNonceResponseValidator
     * @since 2.1.0
     */
    private $responseValidator;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BraintreeAdapter $adapter
     * @param ArrayResultFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param PaymentNonceResponseValidator $responseValidator
     * @since 2.1.0
     */
    public function __construct(
        PaymentTokenManagementInterface $tokenManagement,
        BraintreeAdapter $adapter,
        ArrayResultFactory $resultFactory,
        SubjectReader $subjectReader,
        PaymentNonceResponseValidator $responseValidator
    ) {
        $this->tokenManagement = $tokenManagement;
        $this->adapter = $adapter;
        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
        $this->responseValidator = $responseValidator;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute(array $commandSubject)
    {
        $publicHash = $this->subjectReader->readPublicHash($commandSubject);
        $customerId = $this->subjectReader->readCustomerId($commandSubject);
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if (!$paymentToken) {
            throw new Exception('No available payment tokens');
        }

        $data = $this->adapter->createNonce($paymentToken->getGatewayToken());
        $result = $this->responseValidator->validate(['response' => ['object' => $data]]);

        if (!$result->isValid()) {
            throw new Exception(__(implode("\n", $result->getFailsDescription())));
        }

        return $this->resultFactory->create(['array' => ['paymentMethodNonce' => $data->paymentMethodNonce->nonce]]);
    }
}
