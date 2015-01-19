<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Object\Copy;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class ToOrder converter
 */
class ToOrder
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param OrderBuilder $orderBuilder
     * @param Copy $objectCopyService
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        OrderBuilder $orderBuilder,
        Copy $objectCopyService,
        ManagerInterface $eventManager
    ) {
        $this->orderBuilder = $orderBuilder;
        $this->objectCopyService = $objectCopyService;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderInterface
     */
    public function convert(Address $object, $data = [])
    {
        $orderData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_address',
            'to_order',
            $object
        );
        $order = $this->orderBuilder
            ->populateWithArray(array_merge($orderData, $data))
            ->setStoreId($object->getQuote()->getStoreId())
            ->setQuoteId($object->getQuote()->getId())
            ->create();

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $object->getQuote(), $order);
        $this->eventManager->dispatch(
            'sales_convert_quote_to_order',
            ['order' => $order, 'quote' => $object->getQuote()]
        );
        return $order;

    }
}
