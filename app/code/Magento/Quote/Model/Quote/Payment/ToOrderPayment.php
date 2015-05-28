<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Payment;

use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory as OrderPaymentFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\Object\Copy;
use Magento\Payment\Model\Method\Substitution;

/**
 * Class ToOrderPayment
 */
class ToOrderPayment
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderPaymentFactory
     */
    protected $orderPaymentFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderPaymentFactory $orderPaymentFactory
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderPaymentFactory $orderPaymentFactory,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Payment $object
     * @param array $data
     * @return OrderPaymentInterface
     */
    public function convert(Payment $object, $data = [])
    {
        $paymentData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_payment',
            'to_order_payment',
            $object
        );

        $orderPayment = $this->orderPaymentFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderPayment,
            array_merge($paymentData, $data),
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );
        $orderPayment->setAdditionalInformation(
            array_merge(
                $object->getAdditionalInformation(),
                [Substitution::INFO_KEY_TITLE => $object->getMethodInstance()->getTitle()]
            )
        );
        // set directly on the model
        $orderPayment->setCcNumber($object->getCcNumber());
        $orderPayment->setCcCid($object->getCcCid());

        return $orderPayment;
    }
}
