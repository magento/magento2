<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Since we can't validate 3Dsecure for sequence multishipping orders based on vault tokens,
 * we skip 3D secure verification for vault transactions.
 * For common vault transaction original 3d secure verification builder is called.
 */
class VaultThreeDSecureDataBuilder implements BuilderInterface
{
    /**
     * @var ThreeDSecureDataBuilder
     */
    private $threeDSecureDataBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param ThreeDSecureDataBuilder $threeDSecureDataBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ThreeDSecureDataBuilder $threeDSecureDataBuilder,
        SubjectReader $subjectReader
    ) {
        $this->threeDSecureDataBuilder = $threeDSecureDataBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        if ($payment->getAdditionalInformation('is_multishipping')) {
            return [];
        }

        return $this->threeDSecureDataBuilder->build($buildSubject);
    }
}
