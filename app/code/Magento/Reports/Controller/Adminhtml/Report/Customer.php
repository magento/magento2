<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 *
 * Customer reports admin controller
 *
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Customer extends Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
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
        return match ($this->getRequest()->getActionName()) {
            'exportAccountsCsv', 'exportAccountsExcel', 'accounts' =>
                $this->_authorization->isAllowed('Magento_Reports::accounts'),
            'exportOrdersCsv', 'exportOrdersExcel','orders' =>
                $this->_authorization->isAllowed('Magento_Reports::customers_orders'),
            'exportTotalsCsv', 'exportTotalsExcel', 'totals' =>
                $this->_authorization->isAllowed('Magento_Reports::totals'),
            default =>
                $this->_authorization->isAllowed('Magento_Reports::customers'),
        };
    }
}
