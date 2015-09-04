<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address\Total;

class CollectService
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
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory
    ) {
        $this->totalCollector = $totalCollector;
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->totalListFactory = $totalListFactory;
        $this->totalFactory = $totalFactory;
    }

    /**
     * Get totals collector model
     *
     * @return \Magento\Quote\Model\Quote\Address\Total\Collector
     */
    private function getTotalCollector($storeId)
    {
        if ($this->totalCollector === null) {
            $store = $this->storeManager->getStore($storeId);

            $this->totalCollector = $this->totalCollectorFactory->create(
                ['store' => $store]
            );
        }
        return $this->totalCollector;
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param $storeId
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    public function collect(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, $storeId)
    {
        /** @todo Refactor this code \Magento\Quote\Model\Observer\Frontend\Quote\Address\CollectTotals::dispatch */
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_before',
            [$this->_eventObject => $shippingAssignment->getShipping()->getAddress()] //@todo extend parameters list based on client's code
        );
        /** @var CollectorInterface $collector */
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total');
        foreach ($this->getTotalCollector($storeId)->getCollectors() as $key => $collector) {
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
