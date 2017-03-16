<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 */
class CatalogProductCompareClearObserver implements ObserverInterface
{
    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     */
    protected $_productCompFactory;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     */
    public function __construct(
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
    ) {
        $this->_productCompFactory = $productCompFactory;
    }

    /**
     * Remove All Products from Compare Products
     *
     * Reset count of compared products cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->calculate();

        return $this;
    }
}
