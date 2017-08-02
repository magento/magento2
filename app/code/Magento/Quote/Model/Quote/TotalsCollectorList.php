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
 * Class \Magento\Quote\Model\Quote\TotalsCollectorList
 *
 * @since 2.0.0
 */
class TotalsCollectorList
{

    /**
     * Total models collector
     *
     * @var \Magento\Quote\Model\Quote\Address\Total\Collector
     * @since 2.0.0
     */
    protected $totalCollector;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\CollectorFactory
     * @since 2.0.0
     */
    protected $totalCollectorFactory;

    /**
     * Prefix of model events
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_quote_address';

    /**
     * Name of event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'quote_address';

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     * @since 2.0.0
     */
    protected $totalFactory;

    /**
     * @param Collector $totalCollector
     * @param CollectorFactory $totalCollectorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Address\TotalFactory $totalFactory
     * @since 2.0.0
     */
    public function __construct(
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory
    ) {
        $this->totalCollector = $totalCollector;
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->totalFactory = $totalFactory;
    }

    /**
     * @param int $storeId
     * @return Collector
     * @since 2.0.0
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
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote\Address\Total\AbstractTotal[]
     * @since 2.0.0
     */
    public function getCollectors($storeId)
    {
        return $this->getTotalCollector($storeId)->getCollectors();
    }
}
