<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\Total\CollectorInterface;
use Magento\Quote\Model\Quote\Address\Total\ReaderInterface;

class TotalsReader
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
        'discount',
        'custbalance'
    );

    /**
     * @param Collector $totalCollector
     * @param CollectorFactory $totalCollectorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Address\TotalsListFactory $totalListFactory
     * @param Address\TotalFactory $totalFactory
     * @param TotalsCollectorList $collectorList
     */
    public function __construct(
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalsListFactory $totalListFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        \Magento\Quote\Model\Quote\TotalsCollectorList $collectorList
    ) {
        $this->totalCollector = $totalCollector;
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->totalListFactory = $totalListFactory;
        $this->totalFactory = $totalFactory;
        $this->collectorList = $collectorList;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param $storeId
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote\Address\Total $total, $storeId)
    {
        $output = [];
        /** @var ReaderInterface $reader */
        foreach ($this->collectorList->getCollectors($storeId) as $key => $reader) {
            if (!in_array($key, $this->allowedCollectors)) {
                continue;
            }
            $data = $reader->fetch($total);
            if ($data !== null) {
                $totalInstance = $this->convert($data);
                if (array_key_exists($totalInstance->getCode(), $output)) {
                    $output[$totalInstance->getCode()] = $output[$totalInstance->getCode()]->addData(
                        $totalInstance->getData()
                    );
                } else {
                    $output[$totalInstance->getCode()] = $totalInstance;
                }
            }
        }

        return $output;
    }

    /**
     * @param $total
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    protected function convert($total)
    {
        if ($total instanceof \Magento\Quote\Model\Quote\Address\Total) {
            $totalInstance = $total;
        } else {
            $totalInstance = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total')->setData($total);
        }
        return $totalInstance;
    }
}
