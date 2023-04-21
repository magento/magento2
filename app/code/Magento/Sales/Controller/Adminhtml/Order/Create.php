<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Adminhtml sales orders creation process controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class Create extends \Magento\Backend\App\Action
{
    /**
     * Indicates how to process post data
     */
    private const ACTION_SAVE = 'save';
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $productHelper->setSkipSaleableCheck(true);
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
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
        return $this->_objectManager->get(\Magento\Sales\Model\AdminOrder\Create::class);
    }

    /**
     * Retrieve gift message save model
     *
     * @return \Magento\GiftMessage\Model\Save
     */
    protected function _getGiftmessageSaveModel()
    {
        return $this->_objectManager->get(\Magento\GiftMessage\Model\Save::class);
    }

    /**
     * Initialize order creation session data
     *
     * @return $this
     */
    protected function _initSession()
    {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int)$customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int)$storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string)$currencyId);
            $this->_getOrderCreateModel()->setRecollect(true);
        }
        return $this;
    }

    /**
     * Processing request data
     *
     * @return $this
     */
    protected function _processData()
    {
        return $this->_processActionData();
    }

    /**
     * Process request data with additional logic for saving quote and creating order
     *
     * @param string $action
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _processActionData($action = null)
    {
        $eventData = [
            'order_create_model' => $this->_getOrderCreateModel(),
            'request_model' => $this->getRequest(),
            'session' => $this->_getSession(),
        ];

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_data_before', $eventData);

        /**
         * Import post data, in order to make order quote valid
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
            if ($syncFlag === null
            && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling() && empty($shippingMethod)
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            } elseif ($syncFlag !== null) {
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')
        ) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost(
            'collect_shipping_rates'
        )
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }

        /**
         * Apply mass changes from sidebar
         */
        if (($data = $this->getRequest()->getPost('sidebar')) && $action !== self::ACTION_SAVE) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_item_before', $eventData);

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int)$this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPostValue());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items')
            && $action !== self::ACTION_SAVE
        ) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int)$this->getRequest()->getPost('remove_item');
        $removeFrom = (string)$this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
            $this->_getOrderCreateModel()->recollectCart();
        }

        /**
         * Move quote item
         */
        $moveItemId = (int)$this->getRequest()->getPost('move_item');
        $moveTo = (string)$this->getRequest()->getPost('to');
        $moveQty = (int)$this->getRequest()->getPost('qty');
        if ($moveItemId && $moveTo) {
            $this->_getOrderCreateModel()->moveQuoteItem($moveItemId, $moveTo, $moveQty);
        }

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_item_after', $eventData);

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        $eventData = [
            'order_create_model' => $this->_getOrderCreateModel(),
            'request' => $this->getRequest()->getPostValue(),
        ];

        $this->_eventManager->dispatch('adminhtml_sales_order_create_process_data', $eventData);

        $this->_getOrderCreateModel()->saveQuote();

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        /**
         * Saving of giftmessages
         */
        $giftmessages = $this->getRequest()->getPost('giftmessage');
        if ($giftmessages) {
            $this->_getGiftmessageSaveModel()->setGiftmessages($giftmessages)->saveAllInQuote();
        }

        /**
         * Importing gift message allow items from specific product grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromProducts(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonDecode($data)
            );
        }

        /**
         * Importing gift message allow items on update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromItems($items);
        }

        $data = $this->getRequest()->getPost('order');
        $couponCode = '';
        if (isset($data) && isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }

        if (!empty($couponCode)) {
            $isApplyDiscount = false;
            foreach ($this->_getQuote()->getAllItems() as $item) {
                if (!$item->getNoDiscount()) {
                    $isApplyDiscount = true;
                    break;
                }
            }
            if (!$isApplyDiscount) {
                $this->messageManager->addErrorMessage(
                    __(
                        '"%1" coupon code was not applied. Do not apply discount is selected for item(s)',
                        $this->escaper->escapeHtml($couponCode)
                    )
                );
            } else {
                if ($this->_getQuote()->getCouponCode() !== $couponCode) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'The "%1" coupon code isn\'t valid. Verify the code and try again.',
                            $this->escaper->escapeHtml($couponCode)
                        )
                    );
                } else {
                    $this->messageManager->addSuccessMessage(__('The coupon code has been accepted.'));
                }
            }
        } elseif (isset($data['coupon']['code']) && empty($couponCode)) {
            $this->messageManager->addSuccessMessage(__('The coupon code has been removed.'));
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
        $productHelper = $this->_objectManager->get(\Magento\Catalog\Helper\Product::class);
        foreach ($items as $id => $item) {
            $buyRequest = new \Magento\Framework\DataObject($item);
            $params = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    /**
     * Reload quote
     *
     * @return $this
     */
    protected function _reloadQuote()
    {
        $id = $this->_getQuote()->getId();
        $this->_getQuote()->load($id);
        return $this;
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
        $action = strtolower($this->getRequest()->getActionName() ?? '');
        if (in_array($action, ['index', 'save', 'cancel']) && $this->_getSession()->getReordered()) {
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
}
