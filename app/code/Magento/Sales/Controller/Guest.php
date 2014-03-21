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
namespace Magento\Sales\Controller;

/**
 * Sales orders controller
 */
class Guest extends \Magento\Sales\Controller\AbstractController
{
    /**
     * Try to load valid order and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        return $this->_objectManager->get(
            'Magento\Sales\Helper\Guest'
        )->loadValidOrder(
            $this->_request,
            $this->_response
        );
    }

    /**
     * Check order view availability
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $currentOrder = $this->_coreRegistry->registry('current_order');
        if ($order->getId() && $order->getId() === $currentOrder->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->_view->loadLayout();
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs();
        $this->_view->renderLayout();
    }

    /**
     * Order view form page
     *
     * @return void
     */
    public function formAction()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->_redirect('customer/account/');
            return;
        }
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('head')->setTitle(__('Orders and Returns'));
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs();
        $this->_view->renderLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function printInvoiceAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }

        if ($this->_canViewOrder($order)) {
            if (isset($invoice)) {
                $this->_coreRegistry->register('current_invoice', $invoice);
            }
            $this->_view->loadLayout('print');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/guest/form');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printShipmentAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $shipmentId = (int)$this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
            $order = $shipment->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }
        if ($this->_canViewOrder($order)) {
            if (isset($shipment)) {
                $this->_coreRegistry->register('current_shipment', $shipment);
            }
            $this->_view->loadLayout('print');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/guest/form');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printCreditmemoAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
            $order = $creditmemo->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }

        if ($this->_canViewOrder($order)) {
            if (isset($creditmemo)) {
                $this->_coreRegistry->register('current_creditmemo', $creditmemo);
            }
            $this->_view->loadLayout('print');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/guest/form');
        }
    }
}
