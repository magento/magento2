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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order creditmemo model
 *
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo getResource()
 * @method int getStoreId()
 * @method \Magento\Sales\Model\Order\Creditmemo setStoreId(int $value)
 * @method float getAdjustmentPositive()
 * @method float getBaseShippingTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseShippingTaxAmount(float $value)
 * @method float getStoreToOrderRate()
 * @method \Magento\Sales\Model\Order\Creditmemo setStoreToOrderRate(float $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseDiscountAmount(float $value)
 * @method float getBaseToOrderRate()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseToOrderRate(float $value)
 * @method float getGrandTotal()
 * @method \Magento\Sales\Model\Order\Creditmemo setGrandTotal(float $value)
 * @method float getBaseAdjustmentNegative()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseAdjustmentNegative(float $value)
 * @method float getBaseSubtotalInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseSubtotalInclTax(float $value)
 * @method float getShippingAmount()
 * @method float getSubtotalInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo setSubtotalInclTax(float $value)
 * @method float getAdjustmentNegative()
 * @method float getBaseShippingAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseShippingAmount(float $value)
 * @method float getStoreToBaseRate()
 * @method \Magento\Sales\Model\Order\Creditmemo setStoreToBaseRate(float $value)
 * @method float getBaseToGlobalRate()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseToGlobalRate(float $value)
 * @method float getBaseAdjustment()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseAdjustment(float $value)
 * @method float getBaseSubtotal()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseSubtotal(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setDiscountAmount(float $value)
 * @method float getSubtotal()
 * @method \Magento\Sales\Model\Order\Creditmemo setSubtotal(float $value)
 * @method float getAdjustment()
 * @method \Magento\Sales\Model\Order\Creditmemo setAdjustment(float $value)
 * @method float getBaseGrandTotal()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseGrandTotal(float $value)
 * @method float getBaseAdjustmentPositive()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseAdjustmentPositive(float $value)
 * @method float getBaseTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseTaxAmount(float $value)
 * @method float getShippingTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setShippingTaxAmount(float $value)
 * @method float getTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setTaxAmount(float $value)
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Creditmemo setOrderId(int $value)
 * @method int getEmailSent()
 * @method \Magento\Sales\Model\Order\Creditmemo setEmailSent(int $value)
 * @method int getCreditmemoStatus()
 * @method \Magento\Sales\Model\Order\Creditmemo setCreditmemoStatus(int $value)
 * @method int getState()
 * @method \Magento\Sales\Model\Order\Creditmemo setState(int $value)
 * @method int getShippingAddressId()
 * @method \Magento\Sales\Model\Order\Creditmemo setShippingAddressId(int $value)
 * @method int getBillingAddressId()
 * @method \Magento\Sales\Model\Order\Creditmemo setBillingAddressId(int $value)
 * @method int getInvoiceId()
 * @method \Magento\Sales\Model\Order\Creditmemo setInvoiceId(int $value)
 * @method string getStoreCurrencyCode()
 * @method \Magento\Sales\Model\Order\Creditmemo setStoreCurrencyCode(string $value)
 * @method string getOrderCurrencyCode()
 * @method \Magento\Sales\Model\Order\Creditmemo setOrderCurrencyCode(string $value)
 * @method string getBaseCurrencyCode()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseCurrencyCode(string $value)
 * @method string getGlobalCurrencyCode()
 * @method \Magento\Sales\Model\Order\Creditmemo setGlobalCurrencyCode(string $value)
 * @method string getTransactionId()
 * @method \Magento\Sales\Model\Order\Creditmemo setTransactionId(string $value)
 * @method string getIncrementId()
 * @method \Magento\Sales\Model\Order\Creditmemo setIncrementId(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order\Creditmemo setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order\Creditmemo setUpdatedAt(string $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseHiddenTaxAmount(float $value)
 * @method float getShippingHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo setShippingHiddenTaxAmount(float $value)
 * @method float getBaseShippingHiddenTaxAmnt()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseShippingHiddenTaxAmnt(float $value)
 * @method float getShippingInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo setBaseShippingInclTax(float $value)
 */
namespace Magento\Sales\Model\Order;

class Creditmemo extends \Magento\Sales\Model\AbstractModel
{
    const STATE_OPEN        = 1;
    const STATE_REFUNDED    = 2;
    const STATE_CANCELED    = 3;

    const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/creditmemo/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/creditmemo/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/creditmemo/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/creditmemo/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/creditmemo/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'sales_email/creditmemo/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE        = 'sales_email/creditmemo_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE  = 'sales_email/creditmemo_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY        = 'sales_email/creditmemo_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO         = 'sales_email/creditmemo_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD     = 'sales_email/creditmemo_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED         = 'sales_email/creditmemo_comment/enabled';

    const REPORT_DATE_TYPE_ORDER_CREATED        = 'order_created';
    const REPORT_DATE_TYPE_REFUND_CREATED       = 'refund_created';

    /*
     * Identifier for order history item
     */
    const HISTORY_ENTITY_NAME = 'creditmemo';

    protected static $_states;

    protected $_items;
    protected $_order;
    protected $_comments;

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var array
     */
    protected $_calculators = array();

    protected $_eventPrefix = 'sales_order_creditmemo';
    protected $_eventObject = 'creditmemo';

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Config
     */
    protected $_creditmemoConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Creditmemo\Item\CollectionFactory
     */
    protected $_cmItemCollFactory;

    /**
     * @var \Magento\Core\Model\CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory
     */
    protected $_commentCollFactory;

    /**
     * @var \Magento\Core\Model\Email\Template\MailerFactory
     */
    protected $_templateMailerFactory;

    /**
     * @var \Magento\Core\Model\Email\InfoFactory
     */
    protected $_emailInfoFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Core\Model\LocaleInterface $coreLocale
     * @param \Magento\Sales\Model\Order\Creditmemo\Config $creditmemoConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Resource\Order\Creditmemo\Item\CollectionFactory $cmItemCollFactory
     * @param \Magento\Core\Model\CalculatorFactory $calculatorFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\Creditmemo\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory $commentCollFactory
     * @param \Magento\Core\Model\Email\Template\MailerFactory $templateMailerFactory
     * @param \Magento\Core\Model\Email\InfoFactory $emailInfoFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Core\Model\LocaleInterface $coreLocale,
        \Magento\Sales\Model\Order\Creditmemo\Config $creditmemoConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Resource\Order\Creditmemo\Item\CollectionFactory $cmItemCollFactory,
        \Magento\Core\Model\CalculatorFactory $calculatorFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Creditmemo\CommentFactory $commentFactory,
        \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory $commentCollFactory,
        \Magento\Core\Model\Email\Template\MailerFactory $templateMailerFactory,
        \Magento\Core\Model\Email\InfoFactory $emailInfoFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_paymentData = $paymentData;
        $this->_salesData = $salesData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_creditmemoConfig = $creditmemoConfig;
        $this->_orderFactory = $orderFactory;
        $this->_cmItemCollFactory = $cmItemCollFactory;
        $this->_calculatorFactory = $calculatorFactory;
        $this->_storeManager = $storeManager;
        $this->_commentFactory = $commentFactory;
        $this->_commentCollFactory = $commentCollFactory;
        $this->_templateMailerFactory = $templateMailerFactory;
        $this->_emailInfoFactory = $emailInfoFactory;
        parent::__construct($context, $registry, $coreLocale, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize creditmemo resource model
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Creditmemo');
    }

    /**
     * Retrieve Creditmemo configuration model
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Config
     */
    public function getConfig()
    {
        return $this->_creditmemoConfig;
    }

    /**
     * Retrieve creditmemo store instance
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Declare order for creditmemo
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order\Creditmemo
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve the order the creditmemo for created for
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof \Magento\Sales\Model\Order) {
            $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName(self::HISTORY_ENTITY_NAME);
    }

    /**
     * Retrieve billing address
     *
     * @return \Magento\Sales\Model\Order\Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return \Magento\Sales\Model\Order\Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = $this->_cmItemCollFactory->create()->setCreditmemoFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setCreditmemo($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Returns credit memo item by its order id
     *
     * @param $orderId
     * @return \Magento\Sales\Model\Order\Creditmemo\Item|bool
     */
    public function getItemByOrderId($orderId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getOrderItemId() == $orderId) {
                return $item;
            }
        }
        return false;
    }

    public function addItem(\Magento\Sales\Model\Order\Creditmemo\Item $item)
    {
        $item->setCreditmemo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Creditmemo totals collecting
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function collectTotals()
    {
        foreach ($this->getConfig()->getTotalModels() as $model) {
            $model->collect($this);
        }
        return $this;
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $type = 'regular', $negative = false)
    {
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $this->_calculatorFactory->create(array('store' => $this->getStore()));
            }
            $price = $this->_calculators[$type]->deltaRound($price, $negative);
        }
        return $price;
    }

    public function canRefund()
    {
        if ($this->getState() != self::STATE_CANCELED
            && $this->getState() != self::STATE_REFUNDED
            && $this->getOrder()->getPayment()->canRefund()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check creditmemo cancel action availability
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invoice void action availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return false;
        $canVoid = false;
        if ($this->getState() == self::STATE_REFUNDED) {
            $canVoid = $this->getCanVoidFlag();
            /**
             * If we not retrieve negative answer from payment yet
             */
            if (is_null($canVoid)) {
                $canVoid = $this->getOrder()->getPayment()->canVoid($this);
                if ($canVoid === false) {
                    $this->setCanVoidFlag(false);
                    $this->_saveBeforeDestruct = true;
                }
            } else {
                $canVoid = (bool)$canVoid;
            }
        }
        return $canVoid;
    }


    public function refund()
    {
        $this->setState(self::STATE_REFUNDED);
        $orderRefund = $this->_storeManager->getStore()->roundPrice(
            $this->getOrder()->getTotalRefunded() + $this->getGrandTotal()
        );
        $baseOrderRefund = $this->_storeManager->getStore()->roundPrice(
            $this->getOrder()->getBaseTotalRefunded()+$this->getBaseGrandTotal()
        );

        if ($baseOrderRefund > $this->_storeManager->getStore()->roundPrice($this->getOrder()->getBaseTotalPaid())) {

            $baseAvailableRefund = $this->getOrder()->getBaseTotalPaid()- $this->getOrder()->getBaseTotalRefunded();

            throw new \Magento\Core\Exception(
                __('The most money available to refund is %1.', $this->getOrder()->formatBasePrice($baseAvailableRefund))
            );
        }
        $order = $this->getOrder();
        $order->setBaseTotalRefunded($baseOrderRefund);
        $order->setTotalRefunded($orderRefund);

        $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded()+$this->getBaseSubtotal());
        $order->setSubtotalRefunded($order->getSubtotalRefunded()+$this->getSubtotal());

        $order->setBaseTaxRefunded($order->getBaseTaxRefunded()+$this->getBaseTaxAmount());
        $order->setTaxRefunded($order->getTaxRefunded()+$this->getTaxAmount());
        $order->setBaseHiddenTaxRefunded($order->getBaseHiddenTaxRefunded()+$this->getBaseHiddenTaxAmount());
        $order->setHiddenTaxRefunded($order->getHiddenTaxRefunded()+$this->getHiddenTaxAmount());

        $order->setBaseShippingRefunded($order->getBaseShippingRefunded()+$this->getBaseShippingAmount());
        $order->setShippingRefunded($order->getShippingRefunded()+$this->getShippingAmount());

        $order->setBaseShippingTaxRefunded($order->getBaseShippingTaxRefunded()+$this->getBaseShippingTaxAmount());
        $order->setShippingTaxRefunded($order->getShippingTaxRefunded()+$this->getShippingTaxAmount());

        $order->setAdjustmentPositive($order->getAdjustmentPositive()+$this->getAdjustmentPositive());
        $order->setBaseAdjustmentPositive($order->getBaseAdjustmentPositive()+$this->getBaseAdjustmentPositive());

        $order->setAdjustmentNegative($order->getAdjustmentNegative()+$this->getAdjustmentNegative());
        $order->setBaseAdjustmentNegative($order->getBaseAdjustmentNegative()+$this->getBaseAdjustmentNegative());

        $order->setDiscountRefunded($order->getDiscountRefunded()+$this->getDiscountAmount());
        $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded()+$this->getBaseDiscountAmount());

        if ($this->getInvoice()) {
            $this->getInvoice()->setIsUsedForRefund(true);
            $this->getInvoice()->setBaseTotalRefunded(
                $this->getInvoice()->getBaseTotalRefunded() + $this->getBaseGrandTotal()
            );
            $this->setInvoiceId($this->getInvoice()->getId());
        }

        if (!$this->getPaymentRefundDisallowed()) {
            $order->getPayment()->refund($this);
        }

        $this->_eventManager->dispatch('sales_order_creditmemo_refund', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Cancel Creditmemo action
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function cancel()
    {
        $this->setState(self::STATE_CANCELED);
        foreach ($this->getAllItems() as $item) {
            $item->cancel();
        }
        $this->getOrder()->getPayment()->cancelCreditmemo($this);

        if ($this->getTransactionId()) {
            $this->getOrder()->setTotalOnlineRefunded(
                $this->getOrder()->getTotalOnlineRefunded()-$this->getGrandTotal()
            );
            $this->getOrder()->setBaseTotalOnlineRefunded(
                $this->getOrder()->getBaseTotalOnlineRefunded()-$this->getBaseGrandTotal()
            );
        } else {
            $this->getOrder()->setTotalOfflineRefunded(
                $this->getOrder()->getTotalOfflineRefunded()-$this->getGrandTotal()
            );
            $this->getOrder()->setBaseTotalOfflineRefunded(
                $this->getOrder()->getBaseTotalOfflineRefunded()-$this->getBaseGrandTotal()
            );
        }

        $this->getOrder()->setBaseSubtotalRefunded(
            $this->getOrder()->getBaseSubtotalRefunded()-$this->getBaseSubtotal()
        );
        $this->getOrder()->setSubtotalRefunded($this->getOrder()->getSubtotalRefunded()-$this->getSubtotal());

        $this->getOrder()->setBaseTaxRefunded($this->getOrder()->getBaseTaxRefunded()-$this->getBaseTaxAmount());
        $this->getOrder()->setTaxRefunded($this->getOrder()->getTaxRefunded()-$this->getTaxAmount());

        $this->getOrder()->setBaseShippingRefunded(
            $this->getOrder()->getBaseShippingRefunded()-$this->getBaseShippingAmount()
        );
        $this->getOrder()->setShippingRefunded($this->getOrder()->getShippingRefunded()-$this->getShippingAmount());

        $this->_eventManager->dispatch('sales_order_creditmemo_cancel', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Register creditmemo
     *
     * Apply to order, order items etc.
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @throws \Magento\Core\Exception
     */
    public function register()
    {
        if ($this->getId()) {
            throw new \Magento\Core\Exception(__('We cannot register an existing credit memo.'));
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();
            } else {
                $item->isDeleted(true);
            }
        }

        $this->setDoTransaction(true);
        if ($this->getOfflineRequested()) {
            $this->setDoTransaction(false);
        }
        $this->refund();

        if ($this->getDoTransaction()) {
            $this->getOrder()->setTotalOnlineRefunded(
                $this->getOrder()->getTotalOnlineRefunded() + $this->getGrandTotal()
            );
            $this->getOrder()->setBaseTotalOnlineRefunded(
                $this->getOrder()->getBaseTotalOnlineRefunded() + $this->getBaseGrandTotal()
            );
        } else {
            $this->getOrder()->setTotalOfflineRefunded(
                $this->getOrder()->getTotalOfflineRefunded() + $this->getGrandTotal()
            );
            $this->getOrder()->setBaseTotalOfflineRefunded(
                $this->getOrder()->getBaseTotalOfflineRefunded() + $this->getBaseGrandTotal()
            );
        }

        $this->getOrder()->setBaseTotalInvoicedCost(
            $this->getOrder()->getBaseTotalInvoicedCost() - $this->getBaseCost()
        );

        $state = $this->getState();
        if (is_null($state)) {
            $this->setState(self::STATE_OPEN);
        }
        return $this;
    }

    /**
     * Retrieve Creditmemo states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN       => __('Pending'),
                self::STATE_REFUNDED   => __('Refunded'),
                self::STATE_CANCELED   => __('Canceled'),
            );
        }
        return self::$_states;
    }

    /**
     * Retrieve Creditmemo state name by state identifier
     *
     * @param   int $stateId
     * @return  string
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (is_null(self::$_states)) {
            self::getStates();
        }
        if (isset(self::$_states[$stateId])) {
            return self::$_states[$stateId];
        }
        return __('Unknown State');
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount)
    {
        // base shipping amount calculated in total model
//        $amount = $this->getStore()->roundPrice($amount);
//        $this->setData('base_shipping_amount', $amount);
//
//        $amount = $this->getStore()->roundPrice(
//            $amount*$this->getOrder()->getStoreToOrderRate()
//        );
        $this->setData('shipping_amount', $amount);
        return $this;
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setAdjustmentPositive($amount)
    {
        $amount = trim($amount);
        if (substr($amount, -1) == '%') {
            $amount = (float)substr($amount, 0, -1);
            $amount = $this->getOrder()->getGrandTotal() * $amount / 100;
        }

        $amount = $this->getStore()->roundPrice($amount);
        $this->setData('base_adjustment_positive', $amount);

        $amount = $this->getStore()->roundPrice(
            $amount*$this->getOrder()->getBaseToOrderRate()
        );
        $this->setData('adjustment_positive', $amount);
        return $this;
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setAdjustmentNegative($amount)
    {
        $amount = trim($amount);
        if (substr($amount, -1) == '%') {
            $amount = (float) substr($amount, 0, -1);
            $amount = $this->getOrder()->getGrandTotal() * $amount / 100;
        }

        $amount = $this->getStore()->roundPrice($amount);
        $this->setData('base_adjustment_negative', $amount);

        $amount = $this->getStore()->roundPrice(
            $amount*$this->getOrder()->getBaseToOrderRate()
        );
        $this->setData('adjustment_negative', $amount);
        return $this;
    }

    /**
     * Adds comment to credit memo with additional possibility to send it to customer via email
     * and show it in customer account
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Comment|string $comment
     * @param bool $notify
     * @param bool $visibleOnFront
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Comment
     */
    public function addComment($comment, $notify = false, $visibleOnFront = false)
    {
        if (!($comment instanceof \Magento\Sales\Model\Order\Creditmemo\Comment)) {
            $comment = $this->_commentFactory->create()
                ->setComment($comment)
                ->setIsCustomerNotified($notify)
                ->setIsVisibleOnFront($visibleOnFront);
        }
        $comment->setCreditmemo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }

        return $comment;
    }

    /**
     * @param bool $reload
     * @return \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\Collection
     */
    public function getCommentsCollection($reload = false)
    {
        if (is_null($this->_comments) || $reload) {
            $this->_comments = $this->_commentCollFactory->create()
                ->setCreditmemoFilter($this->getId())
                ->setCreatedAtOrder();
            /**
             * When credit memo created with adding comment,
             * comments collection must be loaded before we added this comment.
             */
            $this->_comments->load();

            if ($this->getId()) {
                foreach ($this->_comments as $comment) {
                    $comment->setCreditmemo($this);
                }
            }
        }
        return $this->_comments;
    }


    /**
     * Send email with creditmemo data
     *
     * @param boolean $notifyCustomer
     * @param string $comment
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        $order = $this->getOrder();
        $storeId = $order->getStore()->getId();

        if (!$this->_salesData->canSendNewCreditmemoEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        $paymentBlockHtml = $this->_paymentData->getInfoBlockHtml($order->getPayment(), $storeId);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = $this->_templateMailerFactory->create();
        if ($notifyCustomer) {
            $emailInfo = $this->_emailInfoFactory->create();
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = $this->_emailInfoFactory->create();
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender($this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'creditmemo'   => $this,
                'comment'      => $comment,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * Send email with creditmemo update information
     *
     * @param boolean $notifyCustomer
     * @param string $comment
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function sendUpdateEmail($notifyCustomer = true, $comment = '')
    {
        $order = $this->getOrder();
        $storeId = $order->getStore()->getId();

        if (!$this->_salesData->canSendCreditmemoCommentEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        $copyMethod = $this->_coreStoreConfig->getConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = $this->_templateMailerFactory->create();
        if ($notifyCustomer) {
            $emailInfo = $this->_emailInfoFactory->create();
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = $this->_emailInfoFactory->create();
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender($this->_coreStoreConfig->getConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order'      => $order,
            'creditmemo' => $this,
            'comment'    => $comment,
            'billing'    => $order->getBillingAddress()
        ));
        $mailer->send();

        return $this;
    }

    /**
     * @param string $configPath
     * @return array|bool
     */
    protected function _getEmails($configPath)
    {
        $data = $this->_coreStoreConfig->getConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * After save object manipulations
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
            }
        }

        if (null !== $this->_comments) {
            foreach($this->_comments as $comment) {
                $comment->save();
            }
        }


        return parent::_afterSave();
    }

    /**
     * Before object save manipulations
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setBillingAddressId($this->getOrder()->getBillingAddress()->getId());
        }

        return $this;
    }

    /**
     * Get creditmemos collection filtered by $filter
     *
     * @param array|null $filter
     * @return \Magento\Sales\Model\Resource\Order\Creditmemo\Collection
     */
    public function getFilteredCollectionItems($filter = null)
    {
        return $this->getResourceCollection()->getFiltered($filter);
    }
}
