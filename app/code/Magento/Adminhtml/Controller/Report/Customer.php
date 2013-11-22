<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * Customer reports admin controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Report;

class Customer extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        if (!$act) {
            $act = 'default';
        }

        $this->_view->loadLayout();
        $this->_addBreadcrumb(
            __('Reports'),
            __('Reports')
        );
        $this->_addBreadcrumb(
            __('Customers'),
            __('Customers')
        );
        return $this;
    }

    public function accountsAction()
    {
        $this->_title->add(__('New Accounts Report'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_customers_accounts')
            ->_addBreadcrumb(
                __('New Accounts'),
                __('New Accounts')
            );
        $this->_view->renderLayout();
    }

    /**
     * Export new accounts report grid to CSV format
     */
    public function exportAccountsCsvAction()
    {
        $this->_view->loadLayout();
        $fileName = 'new_accounts.csv';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export new accounts report grid to Excel XML format
     */
    public function exportAccountsExcelAction()
    {
        $this->_view->loadLayout();
        $fileName = 'new_accounts.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile($fileName));
    }

    public function ordersAction()
    {
        $this->_title->add(__('Order Count Report'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_customers_orders')
            ->_addBreadcrumb(__('Customers by Number of Orders'),
                __('Customers by Number of Orders'));
        $this->_view->renderLayout();
    }

    /**
     * Export customers most ordered report to CSV format
     */
    public function exportOrdersCsvAction()
    {
        $this->_view->loadLayout();
        $fileName = 'customers_orders.csv';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export customers most ordered report to Excel XML format
     */
    public function exportOrdersExcelAction()
    {
        $this->_view->loadLayout();
        $fileName   = 'customers_orders.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile($fileName));
    }

    public function totalsAction()
    {
        $this->_title->add(__('Order Total Report'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_customers_totals')
            ->_addBreadcrumb(__('Customers by Orders Total'),
                __('Customers by Orders Total'));
        $this->_view->renderLayout();
    }

    /**
     * Export customers biggest totals report to CSV format
     */
    public function exportTotalsCsvAction()
    {
        $this->_view->loadLayout();
        $fileName = 'customer_totals.csv';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export customers biggest totals report to Excel XML format
     */
    public function exportTotalsExcelAction()
    {
        $this->_view->loadLayout();
        $fileName = 'customer_totals.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile($fileName));
    }

    protected function _isAllowed()
    {
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
