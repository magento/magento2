<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address\Total;

class Composite
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
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote\Address\TotalsList
     */
    public function collect(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, $storeId)
    {
        /** @todo Refactor this code \Magento\Quote\Model\Observer\Frontend\Quote\Address\CollectTotals::dispatch */
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_before',
            [$this->_eventObject => $shippingAssignment->getShipping()->getAddress()] //@todo extend parameters list based on client's code
        );
        /** @var CollectorInterface $collector */


//        List of collectors:
//
//        Magento\CustomerBalance\Model\Total\Quote\Customerbalance
//        Magento\GiftCardAccount\Model\Total\Quote\Giftcardaccount
//        Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping
//        Magento\GiftWrapping\Model\Total\Quote\Tax\GiftwrappingAfterTax
//        Magento\Msrp\Model\Quote\Address\Total
//        Magento\OfflineShipping\Model\Quote\Freeshipping
//        Magento\Quote\Model\Quote\Address\Total\Subtotal
//        Magento\Quote\Model\Quote\Address\Total\Shipping
//        Magento\Quote\Model\Quote\Address\Total\Grand
//        Magento\Reward\Model\Total\Quote\Reward
//        Magento\SalesRule\Model\Quote\Discount
//        Magento\Tax\Model\Sales\Total\Quote\Subtotal
//        Magento\Tax\Model\Sales\Total\Quote\Shipping
//        Magento\Tax\Model\Sales\Total\Quote\Tax
//        Magento\Weee\Model\Total\Quote\Weee
//        Magento\Weee\Model\Total\Quote\WeeeTax

        $totals = $this->totalListFactory->create();
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */

        foreach ($this->getTotalCollector($storeId)->getCollectors() as $key => $collector) {
            if (!in_array($key, $this->allowedCollectors)) {
                continue;
            }
            $total = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total');
            $collector->collect($shippingAssignment, $total);
            $result = $collector->fetch($total);
            $totals->add($total, $result);
        }

        /**
         * @todo Refactor client's code
         * Magento\Sales\Model\Observer\Frontend\Quote\RestoreCustomerGroupId
         */
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_after',
            [$this->_eventObject => $shippingAssignment->getShipping()->getAddress()]//@todo extend parameters list based on client's code
        );

        return $totals;
    }

    public function getCode()
    {
        return 'composite';
    }
}
