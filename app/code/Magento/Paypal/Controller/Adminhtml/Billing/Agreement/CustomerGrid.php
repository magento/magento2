<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

use Magento\Customer\Controller\RegistryConstants;

class CustomerGrid extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Initialize customer by ID specified in request
     *
     * @return $this
     */
    protected function initCurrentCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        if ($customerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
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
        $this->initCurrentCustomer();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
