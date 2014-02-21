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

/**
 * Adminhtml sales orders creation process controller
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        parent::__construct($context);
        $productHelper->setSkipSaleableCheck(true);
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    /**
     * Retrieve order create model
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create');
    }

    /**
     * Retrieve gift message save model
     *
     * @return \Magento\GiftMessage\Model\Save
     */
    protected function _getGiftmessageSaveModel()
    {
        return $this->_objectManager->get('Magento\GiftMessage\Model\Save');
    }

    /**
     * Initialize order creation session data
     *
     * @return \Magento\Sales\Controller\Adminhtml\Order\Create
     */
    protected function _initSession()
    {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int) $storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string) $currencyId);
            $this->_getOrderCreateModel()->setRecollect(true);
        }
        return $this;
    }

    /**
     * Processing request data
     *
     * @return \Magento\Sales\Controller\Adminhtml\Order\Create
     */
    protected function _processData()
    {
        return $this->_processActionData();
    }

    /**
     * Process request data with additional logic for saving quote and creating order
     *
     * @param string $action
     * @return \Magento\Sales\Controller\Adminhtml\Order\Create
     */
    protected function _processActionData($action = null)
    {
        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request_model'      => $this->getRequest(),
            'session'            => $this->_getSession(),
        );

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_data_before', $eventData);

        /**
         * Saving order data
         */
        if ($data = $this->getRequest()->getPost('order')) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
            $shippingMethod = $this->_getOrderCreateModel()->getShippingAddress()->getShippingMethod();
            if (is_null($syncFlag)
                && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling()
                && empty($shippingMethod)
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            } else {
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()
            && $this->getRequest()->getPost('reset_shipping')) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()
            && $this->getRequest()->getPost('collect_shipping_rates')
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }


        /**
         * Apply mass changes from sidebar
         */
        if ($data = $this->getRequest()->getPost('sidebar')) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int) $this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPost());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item')
            && !$this->getRequest()->getPost('update_items') && !($action == 'save')) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int) $this->getRequest()->getPost('remove_item');
        $removeFrom = (string) $this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
        }

        /**
         * Move quote item
         */
        $moveItemId = (int) $this->getRequest()->getPost('move_item');
        $moveTo = (string) $this->getRequest()->getPost('to');
        $moveQty = (int) $this->getRequest()->getPost('qty');
        if ($moveItemId && $moveTo) {
            $this->_getOrderCreateModel()->moveQuoteItem($moveItemId, $moveTo, $moveQty);
        }

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request'            => $this->getRequest()->getPost(),
        );

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_data', $eventData);

        $this->_getOrderCreateModel()
            ->saveQuote();

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        /**
         * Saving of giftmessages
         */
        $giftmessages = $this->getRequest()->getPost('giftmessage');
        if ($giftmessages) {
            $this->_getGiftmessageSaveModel()->setGiftmessages($giftmessages)
                ->saveAllInQuote();
        }

        /**
         * Importing gift message allow items from specific product grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getGiftmessageSaveModel()
                ->importAllowQuoteItemsFromProducts(
                    $this->_objectManager->get('Magento\Core\Helper\Data')->jsonDecode($data)
                );
        }

        /**
         * Importing gift message allow items on update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromItems($items);
        }

        $data = $this->getRequest()->getPost('order');
        $couponCode = '';
        if (isset($data) && isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }
        if (!empty($couponCode)) {
            if ($this->_getQuote()->getCouponCode() !== $couponCode) {
                $this->messageManager->addError(
                    __('"%1" coupon code is not valid.', $this->_objectManager->get('Magento\Escaper')
                            ->escapeHtml($couponCode)));
            } else {
                $this->messageManager->addSuccess(__('The coupon code has been accepted.'));
            }
        }

        return $this;
    }

    /**
     * Process buyRequest file options of items
     *
     * @param array $items
     * @return array
     */
    protected function _processFiles($items)
    {
        /* @var $productHelper \Magento\Catalog\Helper\Product */
        $productHelper = $this->_objectManager->get('Magento\Catalog\Helper\Product');
        foreach ($items as $id => $item) {
            $buyRequest = new \Magento\Object($item);
            $params = array('files_prefix' => 'item_' . $id . '_');
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    /**
     * Index page
     */
    public function indexAction()
    {
        $this->_title->add(__('Orders'));
        $this->_title->add(__('New Order'));
        $this->_initSession();
        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->renderLayout();
    }


    public function reorderAction()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        if (!$this->_objectManager->get('Magento\Sales\Helper\Reorder')->canReorder($order)) {
            $this->_forward('noroute');
            return;
        }

        if ($order->getId()) {
            $order->setReordered(true);
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);

            $this->_redirect('sales/*');
        } else {
            $this->_redirect('sales/order/');
        }
    }

    protected function _reloadQuote()
    {
        $id = $this->_getQuote()->getId();
        $this->_getQuote()->load($id);
        return $this;
    }

    /**
     * Loading page block
     */
    public function loadBlockAction()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()
                ->_processData();
        } catch (\Magento\Core\Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addException($e, $e->getMessage());
        }


        $asJson= $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->_view->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('sales_order_create_load_block_json');
        } else {
            $update->addHandle('sales_order_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $update->addHandle('sales_order_create_load_block_' . $block);
            }
        }
        $this->_view->loadLayoutUpdates();
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
        $result = $this->_view->getLayout()->renderElement('content');
        if ($request->getParam('as_js_varname')) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setUpdateResult($result);
            $this->_redirect('sales/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }

    /**
     * Adds configured product to quote
     */
    public function addConfiguredAction()
    {
        $errorMessage = null;
        try {
            $this->_initSession()
                ->_processData();
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $errorMessage = $e->getMessage();
        }

        // Form result for client javascript
        $updateResult = new \Magento\Object();
        if ($errorMessage) {
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        } else {
            $updateResult->setOk(true);
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get('Magento\Backend\Model\Session')->setCompositeProductResult($updateResult);
        $this->_redirect('catalog/product/showUpdateResult');
    }

    /**
     * Start order create action
     */
    public function startAction()
    {
        $this->_getSession()->clearStorage();
        $this->_redirect('sales/*', array('customer_id' => $this->getRequest()->getParam('customer_id')));
    }

    /**
     * Cancel order create
     */
    public function cancelAction()
    {
        if ($orderId = $this->_getSession()->getReordered()) {
            $this->_getSession()->clearStorage();
            $this->_redirect('sales/order/view', array(
                'order_id'=>$orderId
            ));
        } else {
            $this->_getSession()->clearStorage();
            $this->_redirect('sales/*');
        }

    }

    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        try {
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL;
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();

            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccess(__('You created the order.'));
            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $this->_redirect('sales/order/view', array('order_id' => $order->getId()));
            } else {
                $this->_redirect('sales/order/index');
            }
        } catch (\Magento\Payment\Model\Info\Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('sales/*/');
        } catch (\Magento\Core\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('sales/*/');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Order saving error: %1', $e->getMessage()));
            $this->_redirect('sales/*/');
        }
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_getAclResource());
    }

    /**
     * Get acl resource
     *
     * @return string
    */
    protected function _getAclResource()
    {
        $action = strtolower($this->getRequest()->getActionName());
        if (in_array($action, array('index', 'save', 'cancel')) && $this->_getSession()->getReordered()) {
            $action = 'reorder';
        }
        switch ($action) {
            case 'index':
            case 'save':
                $aclResource = 'Magento_Sales::create';
                break;
            case 'reorder':
                $aclResource = 'Magento_Sales::reorder';
                break;
            case 'cancel':
                $aclResource = 'Magento_Sales::cancel';
                break;
            default:
                $aclResource = 'Magento_Sales::actions';
                break;
        }
        return $aclResource;
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in order
     *
     * @return \Magento\Sales\Controller\Adminhtml\Order\Create
     */
    public function configureProductToAddAction()
    {
        // Prepare data
        $productId  = (int) $this->getRequest()->getParam('id');

        $configureResult = new \Magento\Object();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $sessionQuote = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
        $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
        $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        // Render page
        $this->_objectManager->get('Magento\Catalog\Helper\Product\Composite')
            ->renderConfigureResult($configureResult);
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in quote items
     *
     * @return \Magento\Sales\Controller\Adminhtml\Order\Create
     */
    public function configureQuoteItemsAction()
    {
        // Prepare data
        $configureResult = new \Magento\Object();
        try {
            $quoteItemId = (int) $this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Core\Exception(__('Quote item id is not received.'));
            }

            $quoteItem = $this->_objectManager->create('Magento\Sales\Model\Quote\Item')->load($quoteItemId);
            if (!$quoteItem->getId()) {
                throw new \Magento\Core\Exception(__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = $this->_objectManager->create('Magento\Sales\Model\Quote\Item\Option')->getCollection()
                    ->addItemFilter(array($quoteItemId));
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        $this->_objectManager->get('Magento\Catalog\Helper\Product\Composite')
            ->renderConfigureResult($configureResult);
    }


    /**
     * Show item update result from loadBlockAction
     * to prevent popup alert with resend data question
     *
     */
    public function showUpdateResultAction()
    {
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if ($session->hasUpdateResult() && is_scalar($session->getUpdateResult())) {
            $this->getResponse()->setBody($session->getUpdateResult());
            $session->unsUpdateResult();
        } else {
            $session->unsUpdateResult();
            return false;
        }
    }

    /**
     * Process data and display index page
     */
    public function processDataAction()
    {
        $this->_initSession();
        $this->_processData();
        $this->_forward('index');
    }
}
