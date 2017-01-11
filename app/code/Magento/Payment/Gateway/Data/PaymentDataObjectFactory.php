<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class PaymentDataObjectFactory implements PaymentDataObjectFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Order\OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @var Quote\QuoteAdapterFactory
     */
    private $quoteAdapterFactory;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Order\OrderAdapterFactory $orderAdapterFactory
     * @param Quote\QuoteAdapterFactory $quoteAdapterFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Order\OrderAdapterFactory $orderAdapterFactory,
        Quote\QuoteAdapterFactory $quoteAdapterFactory
    ) {
        $this->objectManager = $objectManager;
        $this->orderAdapterFactory = $orderAdapterFactory;
        $this->quoteAdapterFactory = $quoteAdapterFactory;
    }

    /**
     * Creates Payment Data Object
     *
     * @param InfoInterface $paymentInfo
     * @return PaymentDataObjectInterface
     */
    public function create(InfoInterface $paymentInfo)
    {
        if ($paymentInfo instanceof Payment) {
            $data['order'] = $this->orderAdapterFactory->create(
                ['order' => $paymentInfo->getOrder()]
            );
        } elseif ($paymentInfo instanceof \Magento\Quote\Model\Quote\Payment) {
            $data['order'] = $this->quoteAdapterFactory->create(
                ['quote' => $paymentInfo->getQuote()]
            );
        }
        $data['payment'] = $paymentInfo;

        return $this->objectManager->create(
            \Magento\Payment\Gateway\Data\PaymentDataObject::class,
            $data
        );
    }
}
