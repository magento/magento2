<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Class \Magento\Paypal\Controller\Adminhtml\Billing\Agreement\CustomerGrid
 *
 * @since 2.0.0
 */
class CustomerGrid extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Initialize customer by ID specified in request
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
