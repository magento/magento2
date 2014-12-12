<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

class CustomerGrid extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Initialize customer by ID specified in request
     *
     * @return $this
     */
    protected function _initCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        if ($customerId) {
            $this->_coreRegistry->register('current_customer_id', $customerId);
        }
        return $this;
    }

    /**
     * Customer billing agreements ajax action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
