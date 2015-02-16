<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Customer;

class View extends \Magento\Review\Controller\Customer
{
    /** @var \Magento\Review\Model\ReviewFactory */
    protected $reviewFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        parent::__construct($context, $customerSession);
        $this->reviewFactory = $reviewFactory;
    }
    /**
     * Render review details
     *
     * @return void
     */
    public function execute()
    {
        $review = $this->reviewFactory->create()->load($this->getRequest()->getParam('id'));
        if ($review->getCustomerId() != $this->_customerSession->getCustomerId()) {
            return $this->_forward('noroute');
        }
        $this->_view->loadLayout();
        if ($navigationBlock = $this->_view->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('review/customer');
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Review Details'));
        $this->_view->renderLayout();
    }
}
