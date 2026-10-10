<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\Quote\Payment;

use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Model\Method\Substitution;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        OrderPaymentRepository $orderPaymentRepository,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        ?SerializerInterface $serializer = null
    ) {
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Convert quote payment to order payment
     *
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

        if (isset($paymentData[OrderPaymentInterface::ADDITIONAL_DATA])
            && is_array($paymentData[OrderPaymentInterface::ADDITIONAL_DATA])
        ) {
            $paymentData[OrderPaymentInterface::ADDITIONAL_DATA] = $this->serializer->serialize(
                $paymentData[OrderPaymentInterface::ADDITIONAL_DATA]
            );
        }

        $orderPayment = $this->orderPaymentRepository->create();
        $this->dataObjectHelper->populateWithArray(
            $orderPayment,
            array_merge($paymentData, $data),
            \Magento\Sales\Api\Data\OrderPaymentInterface::class
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
