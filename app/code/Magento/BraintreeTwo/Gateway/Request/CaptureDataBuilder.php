<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Request;

use Magento\BraintreeTwo\Helper\Formatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CaptureDataBuilder
 */
class CaptureDataBuilder implements BuilderInterface
{
    use Formatter;

    const TRANSACTION_ID = 'transaction_id';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $transactionId = $payment->getCcTransId();
        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        $result = [
            self::TRANSACTION_ID => $transactionId,
            PaymentDataBuilder::AMOUNT => $this->formatPrice(SubjectReader::readAmount($buildSubject))
        ];
        return $result;
    }
}