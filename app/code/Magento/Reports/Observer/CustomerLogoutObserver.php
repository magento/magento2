<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 */
class CustomerLogoutObserver implements ObserverInterface
{
    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     */
    protected $_productCompFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory
     */
    protected $_productIndxFactory;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @param \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory
     */
    public function __construct(
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory
    ) {
        $this->_productCompFactory = $productCompFactory;
        $this->_productIndxFactory = $productIndxFactory;
    }

    /**
     * Customer logout processing
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->purgeVisitorByCustomer()->calculate();
        $this->_productIndxFactory->create()->purgeVisitorByCustomer()->calculate();
    }
}
