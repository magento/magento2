<?php
/**
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     */
    public function __construct(Action\Context $context, Registry $registry)
    {
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Invoice create page
     *
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception(__('The order does not allow an invoice to be created.'));
            }

            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\Order', ['order' => $order])
                ->prepareInvoice($invoiceItems);

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception(__('Cannot create an invoice without products.'));
            }
            $this->registry->register('current_invoice', $invoice);

            $comment = $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoices'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Invoice'));
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, 'Cannot create an invoice.');
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        }
    }
}
