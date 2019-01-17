<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
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
     * @var PassthroughDataObject
     */
    private $passthroughData;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param PassthroughDataObject $passthroughData
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        PassthroughDataObject $passthroughData
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->passthroughData = $passthroughData;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionData = [
            'transactionRequest' => []
        ];

        if ($payment instanceof Payment) {
            if ($payment->getData(Payment::PARENT_TXN_ID)) {
                $authTransaction = $payment->getAuthorizationTransaction();
                $refId = $authTransaction->getAdditionalInformation('real_transaction_id');
                $transactionData['transactionRequest']['transactionType'] = self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE;
                $transactionData['transactionRequest']['refTransId'] = $refId;
            } else {
                $storeId = $this->subjectReader->readStoreId($buildSubject);
                $defaultAction = $this->config->getPaymentAction($storeId) === 'authorize'
                    ? self::REQUEST_AUTH_ONLY
                    : self::REQUEST_AUTH_AND_CAPTURE;

                $transactionData['transactionRequest']['transactionType'] = $defaultAction;
            }

            $this->passthroughData->setData(
                'transactionType',
                $transactionData['transactionRequest']['transactionType']
            );
        }

        return $transactionData;
    }
}
