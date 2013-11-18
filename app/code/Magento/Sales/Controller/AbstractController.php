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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Controller
 */
namespace Magento\Sales\Controller;

abstract class AbstractController extends \Magento\Core\Controller\Front\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Check order view availability
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomerId();
        $availableStates = $this->_objectManager->get('Magento\Sales\Model\Order\Config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, true)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Init layout, messages and set active block for customer
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Magento\Catalog\Model\Session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            $this->_coreRegistry->register('current_order', $order);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Order view page
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * Invoice page
     */
    public function invoiceAction()
    {
        $this->_viewAction();
    }

    /**
     * Shipment page
     */
    public function shipmentAction()
    {
        $this->_viewAction();
    }

    /**
     * Creditmemo page
     */
    public function creditmemoAction()
    {
        $this->_viewAction();
    }

    /**
     * Action for reorder
     */
    public function reorderAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $order = $this->_coreRegistry->registry('current_order');

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Core\Exception $e){
                if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                    $this->_objectManager->get('Magento\Checkout\Model\Session')->addNotice($e->getMessage());
                } else {
                    $this->_objectManager->get('Magento\Checkout\Model\Session')->addError($e->getMessage());
                }
                $this->_redirect('*/*/history');
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Checkout\Model\Session')->addException($e,
                    __('We cannot add this item to your shopping cart.')
                );
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');
    }

    /**
     * Print Order Action
     */
    public function printAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }

    /**
     * Print Invoice Action
     */
    public function printInvoiceAction()
    {
        $invoiceId = (int) $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        }

        if ($this->_canViewOrder($order)) {
            $this->_coreRegistry->register('current_order', $order);
            if (isset($invoice)) {
                $this->_coreRegistry->register('current_invoice', $invoice);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print Shipment Action
     */
    public function printShipmentAction()
    {
        $shipmentId = (int) $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
            $order = $shipment->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        }
        if ($this->_canViewOrder($order)) {
            $this->_coreRegistry->register('current_order', $order);
            if (isset($shipment)) {
                $this->_coreRegistry->register('current_shipment', $shipment);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print Creditmemo Action
     */
    public function printCreditmemoAction()
    {
        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
            $order = $creditmemo->getOrder();
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        }

        if ($this->_canViewOrder($order)) {
            $this->_coreRegistry->register('current_order', $order);
            if (isset($creditmemo)) {
                $this->_coreRegistry->register('current_creditmemo', $creditmemo);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }
}
