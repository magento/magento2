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

        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_view->loadLayout();
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'exportAccountsCsv':
            case 'exportAccountsExcel':
            case 'accounts':
                return $this->_authorization->isAllowed('Magento_Reports::accounts');
            case 'exportOrdersCsv':
            case 'exportOrdersExcel':
            case 'orders':
                return $this->_authorization->isAllowed('Magento_Reports::customers_orders');
            case 'exportTotalsCsv':
            case 'exportTotalsExcel':
            case 'totals':
                return $this->_authorization->isAllowed('Magento_Reports::totals');
            default:
                return $this->_authorization->isAllowed('Magento_Reports::customers');
        }
    }
}
