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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo;

use Magento\App\ResponseInterface;

/**
 * Adminhtml sales orders controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractCreditmemo extends \Magento\Backend\App\Action
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

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Sales\Controller\Adminhtml\Creditmemo
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sales::sales_creditmemo'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Credit Memos'),
            __('Credit Memos')
        );
        return $this;
    }

    /**
     * Creditmemos grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Creditmemo')
        );
        $this->_view->renderLayout();
    }

    /**
     * Creditmemo information page
     *
     * @return void
     */
    public function viewAction()
    {
        if ($creditmemoId = $this->getRequest()->getParam('creditmemo_id')) {
            $this->_forward('view', 'order_creditmemo', null, array('come_from' => 'sales_creditmemo'));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Notify user
     *
     * @return void
     */
    public function emailAction()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
            if ($creditmemo) {
                $creditmemo->sendEmail();
                $historyItem = $this->_objectManager->create(
                    'Magento\Sales\Model\Resource\Order\Status\History\Collection'
                )->getUnnotifiedForInstance(
                    $creditmemo,
                    \Magento\Sales\Model\Order\Creditmemo::HISTORY_ENTITY_NAME
                );
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }

                $this->messageManager->addSuccess(__('We sent the message.'));
                $this->_redirect('sales/order_creditmemo/view', array('creditmemo_id' => $creditmemoId));
            }
        }
    }

    /**
     * @return ResponseInterface|void
     */
    public function pdfcreditmemosAction()
    {
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            $invoices = $this->_objectManager->create(
                'Magento\Sales\Model\Resource\Order\Creditmemo\Collection'
            )->addAttributeToSelect(
                '*'
            )->addAttributeToFilter(
                'entity_id',
                array('in' => $creditmemosIds)
            )->load();
            if (!isset($pdf)) {
                $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')->getPdf($invoices);
            } else {
                $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')->getPdf($invoices);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
            }
            $date = $this->_objectManager->get('Magento\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');

            return $this->_fileFactory->create(
                'creditmemo' . $date . '.pdf',
                $pdf->render(),
                \Magento\App\Filesystem::VAR_DIR,
                'application/pdf'
            );
        }
        $this->_redirect('sales/*/');
    }

    /**
     * @return ResponseInterface|void
     */
    public function printAction()
    {
        /** @see \Magento\Sales\Controller\Adminhtml\Order\Invoice */
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
            if ($creditmemo) {
                $pdf = $this->_objectManager->create(
                    'Magento\Sales\Model\Order\Pdf\Creditmemo'
                )->getPdf(
                    array($creditmemo)
                );
                $date = $this->_objectManager->get('Magento\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create(
                    'creditmemo' . $date . '.pdf',
                    $pdf->render(),
                    \Magento\App\Filesystem::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }
}
