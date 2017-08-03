<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class VaultCaptureDataBuilder
 * @since 2.1.0
 */
class VaultCaptureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     * @since 2.1.0
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @since 2.1.0
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        return [
            'amount' => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
            'paymentMethodToken' => $paymentToken->getGatewayToken()
        ];
    }
}
