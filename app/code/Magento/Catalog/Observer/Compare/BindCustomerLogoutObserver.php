<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Observer\Compare;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Compare\Item;

/**
 * Catalog Compare Item Model
 *
 */
class BindCustomerLogoutObserver implements ObserverInterface
{
    /**
     * @var Item
     */
    private $item;

    /**
     * @param Item $item
     */
    public function __construct(
        Item $item
    ) {
        $this->item = $item;
    }

    /**
     * Customer login bind process
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->item->bindCustomerLogout();

        return $this;
    }
}
