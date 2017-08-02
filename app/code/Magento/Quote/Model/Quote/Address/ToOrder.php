<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;

/**
 * Class ToOrder converter
 * @since 2.0.0
 */
class ToOrder
{
    /**
     * @var Copy
     * @since 2.0.0
     */
    protected $objectCopyService;

    /**
     * @var OrderFactory
     * @since 2.0.0
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param OrderFactory $orderFactory
     * @param Copy $objectCopyService
     * @param ManagerInterface $eventManager
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @since 2.0.0
     */
    public function __construct(
        OrderFactory $orderFactory,
        Copy $objectCopyService,
        ManagerInterface $eventManager,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderFactory = $orderFactory;
        $this->objectCopyService = $objectCopyService;
        $this->eventManager = $eventManager;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderInterface
     * @since 2.0.0
     */
    public function convert(Address $object, $data = [])
    {
        $orderData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order',
            $object
        );
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->orderFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $order,
            array_merge($orderData, $data),
            \Magento\Sales\Api\Data\OrderInterface::class
        );
        $order->setStoreId($object->getQuote()->getStoreId())
            ->setQuoteId($object->getQuote()->getId())
            ->setIncrementId($object->getQuote()->getReservedOrderId());
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote',
            'to_order',
            $object->getQuote(),
            $order
        );
        $this->eventManager->dispatch(
            'sales_convert_quote_to_order',
            ['order' => $order, 'quote' => $object->getQuote()]
        );
        return $order;
    }
}
