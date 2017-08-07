<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\Total\CollectorInterface;

/**
 * Class TotalsCollector
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @param Collector $totalCollector
     * @param CollectorFactory $totalCollectorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Address\TotalFactory $totalFactory
     * @param TotalsCollectorList $collectorList
     * @param \Magento\Quote\Model\ShippingFactory $shippingFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        if ($quote->isVirtual()) {
            return $this->collectAddressTotals($quote, $quote->getBillingAddress());
        }
        return $this->collectAddressTotals($quote, $quote->getShippingAddress());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    public function collect(\Magento\Quote\Model\Quote $quote)
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->totalFactory->create(\Magento\Quote\Model\Quote\Address\Total::class);

        $this->eventManager->dispatch(
            'sales_quote_collect_totals_before',
            ['quote' => $quote]
        );

        $this->_collectItemsQtys($quote);

        $total->setSubtotal(0);
        $total->setBaseSubtotal(0);

        $total->setSubtotalWithDiscount(0);
        $total->setBaseSubtotalWithDiscount(0);

        $total->setGrandTotal(0);
        $total->setBaseGrandTotal(0);

        /** @var \Magento\Quote\Model\Quote\Address $address */
        foreach ($quote->getAllAddresses() as $address) {
            $addressTotal = $this->collectAddressTotals($quote, $address);

            $total->setShippingAmount($addressTotal->getShippingAmount());
            $total->setBaseShippingAmount($addressTotal->getBaseShippingAmount());
            $total->setShippingDescription($addressTotal->getShippingDescription());

            $total->setSubtotal((float)$total->getSubtotal() + $addressTotal->getSubtotal());
            $total->setBaseSubtotal((float)$total->getBaseSubtotal() + $addressTotal->getBaseSubtotal());

            $total->setSubtotalWithDiscount(
                (float)$total->getSubtotalWithDiscount() + $addressTotal->getSubtotalWithDiscount()
            );
            $total->setBaseSubtotalWithDiscount(
                (float)$total->getBaseSubtotalWithDiscount() + $addressTotal->getBaseSubtotalWithDiscount()
            );

            $total->setGrandTotal((float)$total->getGrandTotal() + $addressTotal->getGrandTotal());
            $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $addressTotal->getBaseGrandTotal());
        }

        $this->quoteValidator->validateQuoteAmount($quote, $quote->getGrandTotal());
        $this->quoteValidator->validateQuoteAmount($quote, $quote->getBaseGrandTotal());
        $this->_validateCouponCode($quote);
        $this->eventManager->dispatch(
            'sales_quote_collect_totals_after',
            ['quote' => $quote]
        );
        return $total;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
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
     * @param \Magento\Quote\Model\Quote $quote
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address $address
     * @return Address\Total
     */
    public function collectAddressTotals(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentFactory->create();

        /** @var \Magento\Quote\Api\Data\ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($address->getShippingMethod());
        $shipping->setAddress($address);
        $shippingAssignment->setShipping($shipping);
        $shippingAssignment->setItems($address->getAllItems());

        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->totalFactory->create(\Magento\Quote\Model\Quote\Address\Total::class);
        $this->eventManager->dispatch(
            'sales_quote_address_collect_totals_before',
            [
                'quote' => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total' => $total
            ]
        );

        foreach ($this->collectorList->getCollectors($quote->getStoreId()) as $collector) {
            /** @var CollectorInterface $collector */
            $collector->collect($quote, $shippingAssignment, $total);
        }
        
        $this->eventManager->dispatch(
            'sales_quote_address_collect_totals_after',
            [
                'quote' => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total' => $total
            ]
        );

        $address->addData($total->getData());
        $address->setAppliedTaxes($total->getAppliedTaxes());
        return $total;
    }
}
