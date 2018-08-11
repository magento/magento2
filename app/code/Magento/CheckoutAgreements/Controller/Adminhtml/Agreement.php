<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Agreement extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CheckoutAgreements::checkoutagreement';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_CheckoutAgreements::sales_checkoutagreement'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Checkout Conditions'),
            __('Checkout Terms and Conditions')
        );
        return $this;
    }
}
