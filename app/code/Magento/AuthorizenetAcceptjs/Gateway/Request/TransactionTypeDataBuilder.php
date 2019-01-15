<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the meta transaction information to the request
 */
class TransactionTypeDataBuilder implements BuilderInterface
{
    private const REQUEST_AUTH_AND_CAPTURE = 'authCaptureTransaction';
    private const REQUEST_AUTH_ONLY = 'authOnlyTransaction';
    private const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $transactionData = [
            'transactionRequest' => []
        ];

        if ($payment instanceof Payment) {
            if ($payment->getData(Payment::PARENT_TXN_ID)) {
                $authTransaction = $payment->getAuthorizationTransaction();
                $refId = $authTransaction->getAdditionalInformation(PaymentResponseHandler::REAL_TRANSACTION_ID);
                $transactionData['transactionRequest']['transactionType'] = self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE;
                $transactionData['transactionRequest']['refTransId'] = $refId;
            } else {
                $storeId = $this->subjectReader->readStoreId($buildSubject);
                $defaultAction = $this->config->getPaymentAction($storeId) === 'authorize'
                    ? self::REQUEST_AUTH_ONLY
                    : self::REQUEST_AUTH_AND_CAPTURE;

                $transactionData['transactionRequest']['transactionType'] = $defaultAction;
            }
        }

        return $transactionData;
    }
}
