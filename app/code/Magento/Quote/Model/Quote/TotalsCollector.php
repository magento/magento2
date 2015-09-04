<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\Total\CollectorInterface;

class TotalsCollector
{

    /**
     * Total models collector
     *
     * @var \Magento\Quote\Model\Quote\Address\Total\Collector
     */
    protected $totalCollector;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\CollectorFactory
     */
    protected $totalCollectorFactory;

    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quote_address';

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalsListFactory
     */
    protected $totalListFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     */
    protected $totalFactory;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollectorList
     */
    protected $collectorList;


    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    protected $shippingFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    protected $allowedCollectors = array(
        'subtotal',
        'grand_total'
    );


    public function __construct(
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalsListFactory $totalListFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        \Magento\Quote\Model\Quote\TotalsCollectorList $collectorList,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory
    ) {
        $this->totalCollector = $totalCollector;
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->totalListFactory = $totalListFactory;
        $this->totalFactory = $totalFactory;
        $this->collectorList = $collectorList;
        $this->shippingFactory = $shippingFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return Address\Total
     */
    public function collectQuoteTotals(\Magento\Quote\Model\Quote $quote)
    {
        /** Build shipping assignment DTO  */
        $shippingAssignment = $this->shippingAssignmentFactory->create();
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($quote->getShippingAddress()->getShippingMethod());
        $shipping->setAddress($quote->getShippingAddress());
        $shippingAssignment->setShipping($shipping);
        $shippingAssignment->setItems($quote->getAllItems());
        $total = $this->collectAddressTotals($shippingAssignment, $quote->getStoreId());
        return $total;
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param $storeId
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    public function collectAddressTotals(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, $storeId)
    {
        /** @todo Refactor this code \Magento\Quote\Model\Observer\Frontend\Quote\Address\CollectTotals::dispatch */
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_before',
            [$this->_eventObject => $shippingAssignment->getShipping()->getAddress()] //@todo extend parameters list based on client's code
        );
        /** @var CollectorInterface $collector */
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total');
        foreach ($this->collectorList->getCollectors($storeId) as $key => $collector) {
            if (!in_array($key, $this->allowedCollectors)) {
                continue;
            }
            $collector->collect($shippingAssignment, $total);
        }

        /**
         * @todo Refactor client's code
         * Magento\Sales\Model\Observer\Frontend\Quote\RestoreCustomerGroupId
         */
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_after',
            [$this->_eventObject => $shippingAssignment->getShipping()->getAddress()]//@todo extend parameters list based on client's code
        );
        return $total;
    }
}
