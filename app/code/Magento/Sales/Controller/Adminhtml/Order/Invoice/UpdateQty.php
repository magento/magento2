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

use \Magento\Framework\Model\Exception;
use Magento\Backend\App\Action;

class UpdateQty extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * Update items qty action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $invoiceData = $this->getRequest()->getParam('invoice', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
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
            // Save invoice comment text in current invoice object in order to display it in corresponding view
            $invoiceRawCommentText = $invoiceData['comment_text'];
            $invoice->setCommentText($invoiceRawCommentText);

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoices'));
            $response = $this->_view->getLayout()->getBlock('order_items')->toHtml();
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot update item quantity.'));
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
