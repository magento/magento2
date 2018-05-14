<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Reports\Model\Event\IsReportEnabled
     */
    private $isReportEnabled;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     */
    public function __construct(
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Reports\Model\Event\IsReportEnabled $isReportEnabled
    ) {
        $this->_productCompFactory = $productCompFactory;
        $this->isReportEnabled = $isReportEnabled;
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
        if (!$this->isReportEnabled->execute(\Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW)) {
            return ;
        }

        $this->_productCompFactory->create()->calculate();
    }
}
