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
     * Quote validator
     *
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    protected $shippingFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var array
     */
    protected $allowedCollectors = array(
        'subtotal',
        'grand_total',
        'customerbalance',
        'giftcardaccount',
        'msrp',
        'shipping',
        'freeshipping',
        'pretax_giftwrapping',
        'giftwrapping',
        'tax_giftwrapping',
        'tax_subtotal',
        'tax_shipping',
        'tax',
        'discount'
    );

    /**
     * @param Collector $totalCollector
     * @param CollectorFactory $totalCollectorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Address\TotalsListFactory $totalListFactory
     * @param Address\TotalFactory $totalFactory
     * @param TotalsCollectorList $collectorList
     * @param \Magento\Quote\Model\ShippingFactory $shippingFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     */
    public function __construct(
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalsListFactory $totalListFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        \Magento\Quote\Model\Quote\TotalsCollectorList $collectorList,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator
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
        $this->quoteValidator = $quoteValidator;
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
        return $this->collectAddressTotals($shippingAssignment, $quote->getStoreId());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    public function collect(\Magento\Quote\Model\Quote $quote)
    {

        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
//        $total = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total');

        //protected $_eventPrefix = 'sales_quote';
        //protected $_eventObject = 'quote';

        $this->eventManager->dispatch(
            'sales_quote_collect_totals_before',
            ['quote' => $quote]
        );

        $this->_collectItemsQtys($quote);

//        $total->setSubtotal(0);
//        $total->setBaseSubtotal(0);
//
//        $total->setSubtotalWithDiscount(0);
//        $total->setBaseSubtotalWithDiscount(0);
//
//        $total->setGrandTotal(0);
//        $total->setBaseGrandTotal(0);

        /** @var \Magento\Quote\Model\Quote\Address $address */
        //foreach ($quote->getAllAddresses() as $address) {

            /** Build shipping assignment DTO  */
            $shippingAssignment = $this->shippingAssignmentFactory->create();
            $shipping = $this->shippingFactory->create();
            $shipping->setMethod($quote->getShippingAddress()->getShippingMethod());
            $shipping->setAddress($quote->getShippingAddress());
            $shippingAssignment->setShipping($shipping);
            $shippingAssignment->setItems($quote->getAllItems());

            $addressTotal = $this->collectAddressTotals($shippingAssignment, $quote->getStoreId());

//            $total->setSubtotal((float)$total->getSubtotal() + $addressTotal->getSubtotal());
//            $total->setBaseSubtotal((float)$total->getBaseSubtotal() + $addressTotal->getBaseSubtotal());
//
//            $total->setSubtotalWithDiscount(
//                (float)$total->getSubtotalWithDiscount() + $addressTotal->getSubtotalWithDiscount()
//            );
//            $total->setBaseSubtotalWithDiscount(
//                (float)$total->getBaseSubtotalWithDiscount() + $addressTotal->getBaseSubtotalWithDiscount()
//            );
//
//            $total->setGrandTotal((float)$total->getGrandTotal() + $addressTotal->getGrandTotal());
//            $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $addressTotal->getBaseGrandTotal());
        //}

        $this->quoteValidator->validateQuoteAmount($quote, $quote->getGrandTotal());
        $this->quoteValidator->validateQuoteAmount($quote, $quote->getBaseGrandTotal());

        //$this->setData('trigger_recollect', 0);
        $this->_validateCouponCode($quote);

        //@todo modify arguments
        $this->eventManager->dispatch(
            'sales_quote_collect_totals_after',
            ['quote' => $quote]
        );

        //$this->setTotalsCollectedFlag(true);
        return $addressTotal;
    }

    /**
     * @return $this
     */
    protected function _validateCouponCode(\Magento\Quote\Model\Quote $quote)
    {
        $code = $quote->getData('coupon_code');
        if (strlen($code)) {
            $addressHasCoupon = false;
            $addresses = $quote->getAllAddresses();
            if (count($addresses) > 0) {
                foreach ($addresses as $address) {
                    if ($address->hasCouponCode()) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $quote->setCouponCode('');
                }
            }
        }
        return $this;
    }

    /**
     * Collect items qty
     *
     * @return $this
     */
    protected function _collectItemsQtys(\Magento\Quote\Model\Quote $quote)
    {
        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $child->getQty() * $item->getQty());
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }
            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty((float)$quote->getItemsQty() + $item->getQty());
        }

        return $this;
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
