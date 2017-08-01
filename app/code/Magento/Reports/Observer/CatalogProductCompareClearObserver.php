<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 * @since 2.0.0
 */
class CatalogProductCompareClearObserver implements ObserverInterface
{
    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     * @since 2.0.0
     */
    protected $_productCompFactory;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->calculate();

        return $this;
    }
}
