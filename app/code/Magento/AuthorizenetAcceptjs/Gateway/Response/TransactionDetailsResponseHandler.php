<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionResponseValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the details to the transaction that should show when the transaction is viewed in the admin
 */
class TransactionDetailsResponseHandler implements HandlerInterface
{
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
    public function __construct(SubjectReader $subjectReader, Config $config)
    {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $storeId = $this->subjectReader->readStoreId($handlingSubject);
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $transactionResponse = $response['transactionResponse'];

        if ($payment instanceof Payment) {
            // Add the keys that should show in the transaction details interface
            $additionalInformationKeys = $this->config->getAdditionalInfoKeys($storeId);
            $rawDetails = [];
            foreach ($additionalInformationKeys as $paymentInfoKey) {
                if (isset($transactionResponse[$paymentInfoKey])) {
                    $rawDetails[$paymentInfoKey] = $transactionResponse[$paymentInfoKey];
                    $payment->setAdditionalInformation($paymentInfoKey, $transactionResponse[$paymentInfoKey]);
                }
            }
            $payment->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS, $rawDetails);
        }
    }
}
