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
class CustomerLoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Reports\Model\EventFactory
     * @since 2.0.0
     */
    protected $_eventFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     * @since 2.0.0
     */
    protected $_productCompFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory
     * @since 2.0.0
     */
    protected $_productIndexFactory;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $_customerVisitor;

    /**
     * @param \Magento\Reports\Model\EventFactory $event
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @param \Magento\Reports\Model\Product\Index\ViewedFactory $productIndexFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Reports\Model\EventFactory $event,
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndexFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor
    ) {
        $this->_eventFactory = $event;
        $this->_productCompFactory = $productCompFactory;
        $this->_productIndexFactory = $productIndexFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
    }

    /**
     * Customer login action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return $this;
        }

        $visitorId = $this->_customerVisitor->getId();
        $customerId = $this->_customerSession->getCustomerId();
        $eventModel = $this->_eventFactory->create();
        $eventModel->updateCustomerType($visitorId, $customerId);

        $this->_productCompFactory->create()->updateCustomerFromVisitor()->calculate();
        $this->_productIndexFactory->create()->updateCustomerFromVisitor()->calculate();

        return $this;
    }
}
