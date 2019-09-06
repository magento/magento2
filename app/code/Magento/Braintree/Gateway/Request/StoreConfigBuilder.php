<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\SubjectReader;

/**
 * This builder is used for correct store resolving and used only to retrieve correct store ID.
 * The data from this build won't be send to Braintree Gateway.
 */
class StoreConfigBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            'store_id' => $order->getStoreId()
        ];
    }
}
