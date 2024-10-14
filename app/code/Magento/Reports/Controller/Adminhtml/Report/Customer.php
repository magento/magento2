<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer reports admin controller
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Customer extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and customer breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        if (!$act) {
            $act = 'default';
        }

        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        // phpcs:disable
        switch ($this->getRequest()->getActionName()) {
            case 'accounts':
                return $this->_authorization->isAllowed('Magento_Reports::accounts');
                break;
            case 'orders':
                return $this->_authorization->isAllowed('Magento_Reports::customers_orders');
                break;
            case 'totals':
                return $this->_authorization->isAllowed('Magento_Reports::totals');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::customers');
                break;
        }
    }
}
