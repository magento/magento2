<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Payment;

use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\DataObject\Copy;
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
     * @var OrderPaymentRepository
     */
    protected $orderPaymentRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderPaymentRepository $orderPaymentRepository,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderPaymentRepository = $orderPaymentRepository;
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

        $orderPayment = $this->orderPaymentRepository->create();
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
