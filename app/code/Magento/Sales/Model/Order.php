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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model;

use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Resource\Order\Address\Collection;
use Magento\Sales\Model\Resource\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Sales\Model\Resource\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\Resource\Order\Item\Collection as ImportCollection;
use Magento\Sales\Model\Resource\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\Resource\Order\Shipment\Collection as ShipmentCollection;
use Magento\Sales\Model\Resource\Order\Shipment\Track\Collection as TrackCollection;
use Magento\Sales\Model\Resource\Order\Status\History\Collection as HistoryCollection;

/**
 * Order model
 *
 * Supported events:
 *  sales_order_load_after
 *  sales_order_save_before
 *  sales_order_save_after
 *  sales_order_delete_before
 *  sales_order_delete_after
 *
 * @method \Magento\Sales\Model\Resource\Order _getResource()
 * @method \Magento\Sales\Model\Resource\Order getResource()
 * @method string getState()
 * @method string getStatus()
 * @method \Magento\Sales\Model\Order setStatus(string $value)
 * @method string getCouponCode()
 * @method \Magento\Sales\Model\Order setCouponCode(string $value)
 * @method string getProtectCode()
 * @method \Magento\Sales\Model\Order setProtectCode(string $value)
 * @method string getShippingDescription()
 * @method \Magento\Sales\Model\Order setShippingDescription(string $value)
 * @method int getIsVirtual()
 * @method \Magento\Sales\Model\Order setIsVirtual(int $value)
 * @method int getStoreId()
 * @method \Magento\Sales\Model\Order setStoreId(int $value)
 * @method int getCustomerId()
 * @method \Magento\Sales\Model\Order setCustomerId(int $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Order setBaseDiscountAmount(float $value)
 * @method float getBaseDiscountCanceled()
 * @method \Magento\Sales\Model\Order setBaseDiscountCanceled(float $value)
 * @method float getBaseDiscountInvoiced()
 * @method \Magento\Sales\Model\Order setBaseDiscountInvoiced(float $value)
 * @method float getBaseDiscountRefunded()
 * @method \Magento\Sales\Model\Order setBaseDiscountRefunded(float $value)
 * @method float getBaseGrandTotal()
 * @method \Magento\Sales\Model\Order setBaseGrandTotal(float $value)
 * @method float getBaseShippingAmount()
 * @method \Magento\Sales\Model\Order setBaseShippingAmount(float $value)
 * @method float getBaseShippingCanceled()
 * @method \Magento\Sales\Model\Order setBaseShippingCanceled(float $value)
 * @method float getBaseShippingInvoiced()
 * @method \Magento\Sales\Model\Order setBaseShippingInvoiced(float $value)
 * @method float getBaseShippingRefunded()
 * @method \Magento\Sales\Model\Order setBaseShippingRefunded(float $value)
 * @method float getBaseShippingTaxAmount()
 * @method \Magento\Sales\Model\Order setBaseShippingTaxAmount(float $value)
 * @method float getBaseShippingTaxRefunded()
 * @method \Magento\Sales\Model\Order setBaseShippingTaxRefunded(float $value)
 * @method float getBaseSubtotal()
 * @method \Magento\Sales\Model\Order setBaseSubtotal(float $value)
 * @method float getBaseSubtotalCanceled()
 * @method \Magento\Sales\Model\Order setBaseSubtotalCanceled(float $value)
 * @method float getBaseSubtotalInvoiced()
 * @method \Magento\Sales\Model\Order setBaseSubtotalInvoiced(float $value)
 * @method float getBaseSubtotalRefunded()
 * @method \Magento\Sales\Model\Order setBaseSubtotalRefunded(float $value)
 * @method float getBaseTaxAmount()
 * @method \Magento\Sales\Model\Order setBaseTaxAmount(float $value)
 * @method float getBaseTaxCanceled()
 * @method \Magento\Sales\Model\Order setBaseTaxCanceled(float $value)
 * @method float getBaseTaxInvoiced()
 * @method \Magento\Sales\Model\Order setBaseTaxInvoiced(float $value)
 * @method float getBaseTaxRefunded()
 * @method \Magento\Sales\Model\Order setBaseTaxRefunded(float $value)
 * @method float getBaseToGlobalRate()
 * @method \Magento\Sales\Model\Order setBaseToGlobalRate(float $value)
 * @method float getBaseToOrderRate()
 * @method \Magento\Sales\Model\Order setBaseToOrderRate(float $value)
 * @method float getBaseTotalCanceled()
 * @method \Magento\Sales\Model\Order setBaseTotalCanceled(float $value)
 * @method float getBaseTotalInvoiced()
 * @method \Magento\Sales\Model\Order setBaseTotalInvoiced(float $value)
 * @method float getBaseTotalInvoicedCost()
 * @method \Magento\Sales\Model\Order setBaseTotalInvoicedCost(float $value)
 * @method float getBaseTotalOfflineRefunded()
 * @method \Magento\Sales\Model\Order setBaseTotalOfflineRefunded(float $value)
 * @method float getBaseTotalOnlineRefunded()
 * @method \Magento\Sales\Model\Order setBaseTotalOnlineRefunded(float $value)
 * @method float getBaseTotalPaid()
 * @method \Magento\Sales\Model\Order setBaseTotalPaid(float $value)
 * @method float getBaseTotalQtyOrdered()
 * @method \Magento\Sales\Model\Order setBaseTotalQtyOrdered(float $value)
 * @method float getBaseTotalRefunded()
 * @method \Magento\Sales\Model\Order setBaseTotalRefunded(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Order setDiscountAmount(float $value)
 * @method float getDiscountCanceled()
 * @method \Magento\Sales\Model\Order setDiscountCanceled(float $value)
 * @method float getDiscountInvoiced()
 * @method \Magento\Sales\Model\Order setDiscountInvoiced(float $value)
 * @method float getDiscountRefunded()
 * @method \Magento\Sales\Model\Order setDiscountRefunded(float $value)
 * @method float getGrandTotal()
 * @method \Magento\Sales\Model\Order setGrandTotal(float $value)
 * @method float getShippingAmount()
 * @method \Magento\Sales\Model\Order setShippingAmount(float $value)
 * @method float getShippingCanceled()
 * @method \Magento\Sales\Model\Order setShippingCanceled(float $value)
 * @method float getShippingInvoiced()
 * @method \Magento\Sales\Model\Order setShippingInvoiced(float $value)
 * @method float getShippingRefunded()
 * @method \Magento\Sales\Model\Order setShippingRefunded(float $value)
 * @method float getShippingTaxAmount()
 * @method \Magento\Sales\Model\Order setShippingTaxAmount(float $value)
 * @method float getShippingTaxRefunded()
 * @method \Magento\Sales\Model\Order setShippingTaxRefunded(float $value)
 * @method float getStoreToBaseRate()
 * @method \Magento\Sales\Model\Order setStoreToBaseRate(float $value)
 * @method float getStoreToOrderRate()
 * @method \Magento\Sales\Model\Order setStoreToOrderRate(float $value)
 * @method float getSubtotal()
 * @method \Magento\Sales\Model\Order setSubtotal(float $value)
 * @method float getSubtotalCanceled()
 * @method \Magento\Sales\Model\Order setSubtotalCanceled(float $value)
 * @method float getSubtotalInvoiced()
 * @method \Magento\Sales\Model\Order setSubtotalInvoiced(float $value)
 * @method float getSubtotalRefunded()
 * @method \Magento\Sales\Model\Order setSubtotalRefunded(float $value)
 * @method float getTaxAmount()
 * @method \Magento\Sales\Model\Order setTaxAmount(float $value)
 * @method float getTaxCanceled()
 * @method \Magento\Sales\Model\Order setTaxCanceled(float $value)
 * @method float getTaxInvoiced()
 * @method \Magento\Sales\Model\Order setTaxInvoiced(float $value)
 * @method float getTaxRefunded()
 * @method \Magento\Sales\Model\Order setTaxRefunded(float $value)
 * @method float getTotalCanceled()
 * @method \Magento\Sales\Model\Order setTotalCanceled(float $value)
 * @method float getTotalInvoiced()
 * @method \Magento\Sales\Model\Order setTotalInvoiced(float $value)
 * @method float getTotalOfflineRefunded()
 * @method \Magento\Sales\Model\Order setTotalOfflineRefunded(float $value)
 * @method float getTotalOnlineRefunded()
 * @method \Magento\Sales\Model\Order setTotalOnlineRefunded(float $value)
 * @method float getTotalPaid()
 * @method \Magento\Sales\Model\Order setTotalPaid(float $value)
 * @method float getTotalQtyOrdered()
 * @method \Magento\Sales\Model\Order setTotalQtyOrdered(float $value)
 * @method float getTotalRefunded()
 * @method \Magento\Sales\Model\Order setTotalRefunded(float $value)
 * @method int getCanShipPartially()
 * @method \Magento\Sales\Model\Order setCanShipPartially(int $value)
 * @method int getCanShipPartiallyItem()
 * @method \Magento\Sales\Model\Order setCanShipPartiallyItem(int $value)
 * @method int getCustomerIsGuest()
 * @method \Magento\Sales\Model\Order setCustomerIsGuest(int $value)
 * @method int getCustomerNoteNotify()
 * @method \Magento\Sales\Model\Order setCustomerNoteNotify(int $value)
 * @method int getBillingAddressId()
 * @method \Magento\Sales\Model\Order setBillingAddressId(int $value)
 * @method int getCustomerGroupId()
 * @method \Magento\Sales\Model\Order setCustomerGroupId(int $value)
 * @method int getEditIncrement()
 * @method \Magento\Sales\Model\Order setEditIncrement(int $value)
 * @method int getEmailSent()
 * @method \Magento\Sales\Model\Order setEmailSent(int $value)
 * @method int getForcedShipmentWithInvoice()
 * @method \Magento\Sales\Model\Order setForcedShipmentWithInvoice(int $value)
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order setGiftMessageId(int $value)
 * @method int getPaymentAuthExpiration()
 * @method \Magento\Sales\Model\Order setPaymentAuthExpiration(int $value)
 * @method int getQuoteAddressId()
 * @method \Magento\Sales\Model\Order setQuoteAddressId(int $value)
 * @method int getQuoteId()
 * @method \Magento\Sales\Model\Order setQuoteId(int $value)
 * @method int getShippingAddressId()
 * @method \Magento\Sales\Model\Order setShippingAddressId(int $value)
 * @method float getAdjustmentNegative()
 * @method \Magento\Sales\Model\Order setAdjustmentNegative(float $value)
 * @method float getAdjustmentPositive()
 * @method \Magento\Sales\Model\Order setAdjustmentPositive(float $value)
 * @method float getBaseAdjustmentNegative()
 * @method \Magento\Sales\Model\Order setBaseAdjustmentNegative(float $value)
 * @method float getBaseAdjustmentPositive()
 * @method \Magento\Sales\Model\Order setBaseAdjustmentPositive(float $value)
 * @method float getBaseShippingDiscountAmount()
 * @method \Magento\Sales\Model\Order setBaseShippingDiscountAmount(float $value)
 * @method float getBaseSubtotalInclTax()
 * @method \Magento\Sales\Model\Order setBaseSubtotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order setBaseTotalDue(float $value)
 * @method float getPaymentAuthorizationAmount()
 * @method \Magento\Sales\Model\Order setPaymentAuthorizationAmount(float $value)
 * @method float getShippingDiscountAmount()
 * @method \Magento\Sales\Model\Order setShippingDiscountAmount(float $value)
 * @method float getSubtotalInclTax()
 * @method \Magento\Sales\Model\Order setSubtotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order setTotalDue(float $value)
 * @method float getWeight()
 * @method \Magento\Sales\Model\Order setWeight(float $value)
 * @method string getCustomerDob()
 * @method \Magento\Sales\Model\Order setCustomerDob(string $value)
 * @method \Magento\Sales\Model\Order setIncrementId(string $value)
 * @method string getAppliedRuleIds()
 * @method \Magento\Sales\Model\Order setAppliedRuleIds(string $value)
 * @method string getBaseCurrencyCode()
 * @method \Magento\Sales\Model\Order setBaseCurrencyCode(string $value)
 * @method string getCustomerEmail()
 * @method \Magento\Sales\Model\Order setCustomerEmail(string $value)
 * @method string getCustomerFirstname()
 * @method \Magento\Sales\Model\Order setCustomerFirstname(string $value)
 * @method string getCustomerLastname()
 * @method \Magento\Sales\Model\Order setCustomerLastname(string $value)
 * @method string getCustomerMiddlename()
 * @method \Magento\Sales\Model\Order setCustomerMiddlename(string $value)
 * @method string getCustomerPrefix()
 * @method \Magento\Sales\Model\Order setCustomerPrefix(string $value)
 * @method string getCustomerSuffix()
 * @method \Magento\Sales\Model\Order setCustomerSuffix(string $value)
 * @method string getCustomerTaxvat()
 * @method \Magento\Sales\Model\Order setCustomerTaxvat(string $value)
 * @method string getDiscountDescription()
 * @method \Magento\Sales\Model\Order setDiscountDescription(string $value)
 * @method string getExtCustomerId()
 * @method \Magento\Sales\Model\Order setExtCustomerId(string $value)
 * @method string getExtOrderId()
 * @method \Magento\Sales\Model\Order setExtOrderId(string $value)
 * @method string getGlobalCurrencyCode()
 * @method \Magento\Sales\Model\Order setGlobalCurrencyCode(string $value)
 * @method string getHoldBeforeState()
 * @method \Magento\Sales\Model\Order setHoldBeforeState(string $value)
 * @method string getHoldBeforeStatus()
 * @method \Magento\Sales\Model\Order setHoldBeforeStatus(string $value)
 * @method string getOrderCurrencyCode()
 * @method \Magento\Sales\Model\Order setOrderCurrencyCode(string $value)
 * @method string getOriginalIncrementId()
 * @method \Magento\Sales\Model\Order setOriginalIncrementId(string $value)
 * @method string getRelationChildId()
 * @method \Magento\Sales\Model\Order setRelationChildId(string $value)
 * @method string getRelationChildRealId()
 * @method \Magento\Sales\Model\Order setRelationChildRealId(string $value)
 * @method string getRelationParentId()
 * @method \Magento\Sales\Model\Order setRelationParentId(string $value)
 * @method string getRelationParentRealId()
 * @method \Magento\Sales\Model\Order setRelationParentRealId(string $value)
 * @method string getRemoteIp()
 * @method \Magento\Sales\Model\Order setRemoteIp(string $value)
 * @method \Magento\Sales\Model\Order setShippingMethod(string $value)
 * @method string getStoreCurrencyCode()
 * @method \Magento\Sales\Model\Order setStoreCurrencyCode(string $value)
 * @method string getStoreName()
 * @method \Magento\Sales\Model\Order setStoreName(string $value)
 * @method string getXForwardedFor()
 * @method \Magento\Sales\Model\Order setXForwardedFor(string $value)
 * @method string getCustomerNote()
 * @method \Magento\Sales\Model\Order setCustomerNote(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order setUpdatedAt(string $value)
 * @method int getTotalItemCount()
 * @method \Magento\Sales\Model\Order setTotalItemCount(int $value)
 * @method int getCustomerGender()
 * @method \Magento\Sales\Model\Order setCustomerGender(int $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order setBaseHiddenTaxAmount(float $value)
 * @method float getShippingHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order setShippingHiddenTaxAmount(float $value)
 * @method float getBaseShippingHiddenTaxAmnt()
 * @method \Magento\Sales\Model\Order setBaseShippingHiddenTaxAmnt(float $value)
 * @method float getHiddenTaxInvoiced()
 * @method \Magento\Sales\Model\Order setHiddenTaxInvoiced(float $value)
 * @method float getBaseHiddenTaxInvoiced()
 * @method \Magento\Sales\Model\Order setBaseHiddenTaxInvoiced(float $value)
 * @method float getHiddenTaxRefunded()
 * @method \Magento\Sales\Model\Order setHiddenTaxRefunded(float $value)
 * @method float getBaseHiddenTaxRefunded()
 * @method \Magento\Sales\Model\Order setBaseHiddenTaxRefunded(float $value)
 * @method float getShippingInclTax()
 * @method \Magento\Sales\Model\Order setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\Sales\Model\Order setBaseShippingInclTax(float $value)
 * @method bool hasBillingAddressId()
 * @method \Magento\Sales\Model\Order unsBillingAddressId()
 * @method bool hasShippingAddressId()
 * @method \Magento\Sales\Model\Order unsShippingAddressId()
 * @method int getShippigAddressId()
 * @method bool hasCustomerNoteNotify()
 * @method bool hasForcedCanCreditmemo()
 * @method bool getIsInProcess()
 * @method \Magento\Customer\Model\Customer getCustomer()
 */
class Order extends \Magento\Sales\Model\AbstractModel implements EntityInterface
{
    const ENTITY = 'order';

    /**
     * Order states
     */
    const STATE_NEW = 'new';

    const STATE_PENDING_PAYMENT = 'pending_payment';

    const STATE_PROCESSING = 'processing';

    const STATE_COMPLETE = 'complete';

    const STATE_CLOSED = 'closed';

    const STATE_CANCELED = 'canceled';

    const STATE_HOLDED = 'holded';

    const STATE_PAYMENT_REVIEW = 'payment_review';

    /**
     * Order statuses
     */
    const STATUS_FRAUD = 'fraud';

    /**
     * Order flags
     */
    const ACTION_FLAG_CANCEL = 'cancel';

    const ACTION_FLAG_HOLD = 'hold';

    const ACTION_FLAG_UNHOLD = 'unhold';

    const ACTION_FLAG_EDIT = 'edit';

    const ACTION_FLAG_CREDITMEMO = 'creditmemo';

    const ACTION_FLAG_INVOICE = 'invoice';

    const ACTION_FLAG_REORDER = 'reorder';

    const ACTION_FLAG_SHIP = 'ship';

    const ACTION_FLAG_COMMENT = 'comment';

    /**
     * Report date types
     */
    const REPORT_DATE_TYPE_CREATED = 'created';

    const REPORT_DATE_TYPE_UPDATED = 'updated';

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order';

    /**
     * @var string
     */
    protected $_eventObject = 'order';

    /**
     * @var  Collection|null
     */
    protected $_addresses = null;

    /**
     * @var ImportCollection|null
     */
    protected $_items = null;

    /**
     * @var PaymentCollection|null
     */
    protected $_payments = null;

    /**
     * @var HistoryCollection|null
     */
    protected $_statusHistory = null;

    /**
     * @var InvoiceCollection
     */
    protected $_invoices;

    /**
     * @var TrackCollection
     */
    protected $_tracks;

    /**
     * @var ShipmentCollection
     */
    protected $_shipments;

    /**
     * @var CreditmemoCollection
     */
    protected $_creditmemos;

    /**
     * @var array
     */
    protected $_relatedObjects = array();

    /**
     * @var Currency
     */
    protected $_orderCurrency = null;

    /**
     * @var Currency|null
     */
    protected $_baseCurrency = null;

    /**
     * Array of action flags for canUnhold, canEdit, etc.
     *
     * @var array
     */
    protected $_actionFlag = array();

    /**
     * Flag: if after order placing we can send new email to the customer.
     *
     * @var bool
     */
    protected $_canSendNewEmailFlag = true;

    /**
     * Identifier for history item
     *
     * @var string
     */
    protected $entityType = 'order';

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalculation;

    /**
     * @var \Magento\Sales\Model\Service\OrderFactory
     */
    protected $_serviceOrderFactory;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory
     */
    protected $_orderTaxCollectionFactory;

    /**
     * @var Resource\Order\Address\CollectionFactory
     */
    protected $_addressCollectionFactory;

    /**
     * @var Resource\Order\Payment\CollectionFactory
     */
    protected $_paymentCollectionFactory;

    /**
     * @var Resource\Order\Status\History\CollectionFactory
     */
    protected $_historyCollectionFactory;

    /**
     * @var Resource\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var Resource\Order\Shipment\CollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var Resource\Order\Creditmemo\CollectionFactory
     */
    protected $_memoCollectionFactory;

    /**
     * @var Resource\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Order\Config $orderConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Resource\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param Service\OrderFactory $serviceOrderFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Order\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory
     * @param Resource\Order\Address\CollectionFactory $addressCollectionFactory
     * @param Resource\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param Resource\Order\Status\History\CollectionFactory $historyCollectionFactory
     * @param Resource\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param Resource\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param Resource\Order\Creditmemo\CollectionFactory $memoCollectionFactory
     * @param Resource\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\Resource\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Sales\Model\Service\OrderFactory $serviceOrderFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_orderConfig = $orderConfig;
        $this->_productFactory = $productFactory;

        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_productVisibility = $productVisibility;
        $this->_taxCalculation = $taxCalculation;
        $this->_serviceOrderFactory = $serviceOrderFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->_eavConfig = $eavConfig;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_orderTaxCollectionFactory = $orderTaxCollectionFactory;
        $this->_addressCollectionFactory = $addressCollectionFactory;
        $this->_paymentCollectionFactory = $paymentCollectionFactory;
        $this->_historyCollectionFactory = $historyCollectionFactory;
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_memoCollectionFactory = $memoCollectionFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $registry, $localeDate, $dateTime, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order');
    }

    /**
     * Clear order object data
     *
     * @param string $key data key
     * @return $this
     */
    public function unsetData($key = null)
    {
        parent::unsetData($key);
        if (is_null($key)) {
            $this->_items = null;
        }
        return $this;
    }

    /**
     * Retrieve can flag for action (edit, unhold, etc..)
     *
     * @param string $action
     * @return boolean|null
     */
    public function getActionFlag($action)
    {
        if (isset($this->_actionFlag[$action])) {
            return $this->_actionFlag[$action];
        }
        return null;
    }

    /**
     * Set can flag value for action (edit, unhold, etc...)
     *
     * @param string $action
     * @param boolean $flag
     * @return $this
     */
    public function setActionFlag($action, $flag)
    {
        $this->_actionFlag[$action] = (bool)$flag;
        return $this;
    }

    /**
     * Return flag for order if it can sends new email to customer.
     *
     * @return bool
     */
    public function getCanSendNewEmailFlag()
    {
        return $this->_canSendNewEmailFlag;
    }

    /**
     * Set flag for order if it can sends new email to customer.
     *
     * @param bool $flag
     * @return $this
     */
    public function setCanSendNewEmailFlag($flag)
    {
        $this->_canSendNewEmailFlag = (bool)$flag;
        return $this;
    }

    /**
     * Load order by system increment identifier
     *
     * @param string $incrementId
     * @return \Magento\Sales\Model\Order
     */
    public function loadByIncrementId($incrementId)
    {
        return $this->loadByAttribute('increment_id', $incrementId);
    }

    /**
     * Load order by custom attribute value. Attribute value should be unique
     *
     * @param string $attribute
     * @param string $value
     * @return $this
     */
    public function loadByAttribute($attribute, $value)
    {
        $this->load($value, $attribute);
        return $this;
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Retrieve order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        if (!$this->_canVoidOrder()) {
            return false;
        }
        if ($this->canUnhold()) {
            return false;
        }
        if (!$this->canReviewPayment() && $this->canFetchPaymentReviewUpdate()) {
            return false;
        }

        $allInvoiced = true;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        return true;
    }

    /**
     * Getter whether the payment can be voided
     * @return bool
     */
    public function canVoidPayment()
    {
        return $this->_canVoidOrder() ? $this->getPayment()->canVoid($this->getPayment()) : false;
    }

    /**
     * Check whether order could be canceled by states and flags
     *
     * @return bool
     */
    protected function _canVoidOrder()
    {
        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order invoice availability
     *
     * @return bool
     */
    public function canInvoice()
    {
        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }
        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_INVOICE) === false) {
            return false;
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order credit memo (refund) availability
     *
     * @return bool
     */
    public function canCreditmemo()
    {
        if ($this->hasForcedCanCreditmemo()) {
            return $this->getForcedCanCreditmemo();
        }

        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }

        if ($this->isCanceled() || $this->getState() === self::STATE_CLOSED) {
            return false;
        }

        /**
         * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
         * for this we have additional diapason for 0
         * TotalPaid - contains amount, that were not rounded.
         */
        if (abs($this->priceCurrency->round($this->getTotalPaid()) - $this->getTotalRefunded()) < .0001) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_EDIT) === false) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order hold availability
     *
     * @return bool
     */
    public function canHold()
    {
        $state = $this->getState();
        if ($this->isCanceled() ||
            $this->isPaymentReview() ||
            $state === self::STATE_COMPLETE ||
            $state === self::STATE_CLOSED ||
            $state === self::STATE_HOLDED
        ) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_HOLD) === false) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order unhold availability
     *
     * @return bool
     */
    public function canUnhold()
    {
        if ($this->getActionFlag(self::ACTION_FLAG_UNHOLD) === false || $this->isPaymentReview()) {
            return false;
        }
        return $this->getState() === self::STATE_HOLDED;
    }

    /**
     * Check if comment can be added to order history
     *
     * @return bool
     */
    public function canComment()
    {
        if ($this->getActionFlag(self::ACTION_FLAG_COMMENT) === false) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     */
    public function canShip()
    {
        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }

        if ($this->getIsVirtual() || $this->isCanceled()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_SHIP) === false) {
            return false;
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() && !$item->getLockedDoShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order edit availability
     *
     * @return bool
     */
    public function canEdit()
    {
        if ($this->canUnhold()) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() ||
            $this->isPaymentReview() ||
            $state === self::STATE_COMPLETE ||
            $state === self::STATE_CLOSED
        ) {
            return false;
        }

        if (!$this->getPayment()->getMethodInstance()->canEdit()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_EDIT) === false) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve order reorder availability
     *
     * @return bool
     */
    public function canReorder()
    {
        return $this->_canReorder(false);
    }

    /**
     * Check the ability to reorder ignoring the availability in stock or status of the ordered products
     *
     * @return bool
     */
    public function canReorderIgnoreSalable()
    {
        return $this->_canReorder(true);
    }

    /**
     * Retrieve order reorder availability
     *
     * @param bool $ignoreSalable
     * @return bool
     */
    protected function _canReorder($ignoreSalable = false)
    {
        if ($this->canUnhold() || $this->isPaymentReview() || !$this->getCustomerId()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_REORDER) === false) {
            return false;
        }

        $products = array();
        foreach ($this->getItemsCollection() as $item) {
            $products[] = $item->getProductId();
        }

        if (!empty($products)) {
            /*
             * @TODO ACPAOC: Use product collection here, but ensure that product
             * is loaded with order store id, otherwise there'll be problems with isSalable()
             * for composite products
             *
             */
            /*
            $productsCollection = $this->_productFactory->create()->getCollection()
                ->setStoreId($this->getStoreId())
                ->addIdFilter($products)
                ->addAttributeToSelect('status')
                ->load();

            foreach ($productsCollection as $product) {
                if (!$product->isSalable()) {
                    return false;
                }
            }
            */

            foreach ($products as $productId) {
                $product = $this->_productFactory->create()->setStoreId($this->getStoreId())->load($productId);
            }
            if (!$product->getId() || !$ignoreSalable && !$product->isSalable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether the payment is in payment review state
     * In this state order cannot be normally processed. Possible actions can be:
     * - accept or deny payment
     * - fetch transaction information
     *
     * @return bool
     */
    public function isPaymentReview()
    {
        return $this->getState() === self::STATE_PAYMENT_REVIEW;
    }

    /**
     * Check whether payment can be accepted or denied
     *
     * @return bool
     */
    public function canReviewPayment()
    {
        return $this->isPaymentReview() && $this->getPayment()->canReviewPayment();
    }

    /**
     * Check whether there can be a transaction update fetched for payment in review state
     *
     * @return bool
     */
    public function canFetchPaymentReviewUpdate()
    {
        return $this->isPaymentReview() && $this->getPayment()->canFetchTransactionInfo();
    }

    /**
     * Retrieve order configuration model
     *
     * @return \Magento\Sales\Model\Order\Config
     */
    public function getConfig()
    {
        return $this->_orderConfig;
    }

    /**
     * Place order payments
     *
     * @return $this
     */
    protected function _placePayment()
    {
        $this->getPayment()->place();
        return $this;
    }

    /**
     * Retrieve order payment model object
     *
     * @return Payment|false
     */
    public function getPayment()
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if (!$payment->isDeleted()) {
                return $payment;
            }
        }
        return false;
    }

    /**
     * Declare order billing address
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return $this
     */
    public function setBillingAddress(\Magento\Sales\Model\Order\Address $address)
    {
        $old = $this->getBillingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $address->setEmail($this->getCustomerEmail());
        $this->addAddress($address->setAddressType('billing'));
        return $this;
    }

    /**
     * Declare order shipping address
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return $this
     */
    public function setShippingAddress(\Magento\Sales\Model\Order\Address $address)
    {
        $old = $this->getShippingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $address->setEmail($this->getCustomerEmail());
        $this->addAddress($address->setAddressType('shipping'));
        return $this;
    }

    /**
     * Retrieve order billing address
     *
     * @return \Magento\Sales\Model\Order\Address|false
     */
    public function getBillingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType() == 'billing' && !$address->isDeleted()) {
                return $address;
            }
        }
        return false;
    }

    /**
     * Retrieve order shipping address
     *
     * @return \Magento\Sales\Model\Order\Address|false
     */
    public function getShippingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType() == 'shipping' && !$address->isDeleted()) {
                return $address;
            }
        }
        return false;
    }

    /**
     * Order state setter.
     * If status is specified, will add order status history with specified comment
     * the setData() cannot be overridden because of compatibility issues with resource model
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @param bool $shouldProtectState
     * @return \Magento\Sales\Model\Order
     */
    public function setState(
        $state,
        $status = false,
        $comment = '',
        $isCustomerNotified = null,
        $shouldProtectState = true
    ) {
        return $this->_setState($state, $status, $comment, $isCustomerNotified, $shouldProtectState);
    }

    /**
     * Order state protected setter.
     * By default allows to set any state. Can also update status to default or specified value
     * Complete and closed states are encapsulated intentionally, see the _checkState()
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @param bool $shouldProtectState
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _setState(
        $state,
        $status = false,
        $comment = '',
        $isCustomerNotified = null,
        $shouldProtectState = false
    ) {
        // attempt to set the specified state
        if ($shouldProtectState) {
            if ($this->isStateProtected($state)) {
                throw new \Magento\Framework\Model\Exception(__('The Order State "%1" must not be set manually.', $state));
            }
        }
        $this->setData('state', $state);

        // add status history
        if ($status) {
            if ($status === true) {
                $status = $this->getConfig()->getStateDefaultStatus($state);
            }
            $this->setStatus($status);
            $history = $this->addStatusHistoryComment($comment, false);
            // no sense to set $status again
            $history->setIsCustomerNotified($isCustomerNotified);
        }
        return $this;
    }

    /**
     * Whether specified state can be set from outside
     * @param string $state
     * @return bool
     */
    public function isStateProtected($state)
    {
        if (empty($state)) {
            return false;
        }
        return self::STATE_COMPLETE == $state || self::STATE_CLOSED == $state;
    }

    /**
     * Retrieve label of order status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getConfig()->getStatusLabel($this->getStatus());
    }

    /**
     * Add status change information to history
     *
     * @param  string $status
     * @param  string $comment
     * @param  bool $isCustomerNotified
     * @return $this
     */
    public function addStatusToHistory($status, $comment = '', $isCustomerNotified = false)
    {
        $this->addStatusHistoryComment($comment, $status)->setIsCustomerNotified($isCustomerNotified);
        return $this;
    }

    /**
     * Add a comment to order
     * Different or default status may be specified
     *
     * @param string $comment
     * @param bool|string $status
     * @return \Magento\Sales\Model\Order\Status\History
     */
    public function addStatusHistoryComment($comment, $status = false)
    {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }
        $history = $this->_orderHistoryFactory->create()->setStatus(
            $status
        )->setComment(
            $comment
        )->setEntityName(
            $this->entityType
        );
        $this->addStatusHistory($history);
        return $history;
    }

    /**
     * Overrides entity id, which will be saved to comments history status
     *
     * @param string $entityName
     * @return $this
     */
    public function setHistoryEntityName($entityName)
    {
        $this->entityType = $entityName;
        return $this;
    }

    /**
     * Return order entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Place order
     *
     * @return $this
     */
    public function place()
    {
        $this->_eventManager->dispatch('sales_order_place_before', array('order' => $this));
        $this->_placePayment();
        $this->_eventManager->dispatch('sales_order_place_after', array('order' => $this));
        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function hold()
    {
        if (!$this->canHold()) {
            throw new \Magento\Framework\Model\Exception(__('A hold action is not available.'));
        }
        $this->setHoldBeforeState($this->getState());
        $this->setHoldBeforeStatus($this->getStatus());
        $this->setState(self::STATE_HOLDED, true);
        return $this;
    }

    /**
     * Attempt to unhold the order
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function unhold()
    {
        if (!$this->canUnhold()) {
            throw new \Magento\Framework\Model\Exception(__('You cannot remove the hold.'));
        }
        $this->setState($this->getHoldBeforeState(), $this->getHoldBeforeStatus());
        $this->setHoldBeforeState(null);
        $this->setHoldBeforeStatus(null);
        return $this;
    }

    /**
     * Cancel order
     *
     * @return $this
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            $this->getPayment()->cancel();
            $this->registerCancellation();

            $this->_eventManager->dispatch('order_cancel_after', array('order' => $this));
        }

        return $this;
    }

    /**
     * Prepare order totals to cancellation
     * @param string $comment
     * @param bool $graceful
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function registerCancellation($comment = '', $graceful = true)
    {
        if ($this->canCancel() || $this->isPaymentReview()) {
            $cancelState = self::STATE_CANCELED;
            foreach ($this->getAllItems() as $item) {
                if ($cancelState != self::STATE_PROCESSING && $item->getQtyToRefund()) {
                    if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                        $cancelState = self::STATE_PROCESSING;
                    } else {
                        $cancelState = self::STATE_COMPLETE;
                    }
                }
                $item->cancel();
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());

            $this->_setState($cancelState, true, $comment);
        } elseif (!$graceful) {
            throw new \Magento\Framework\Model\Exception(__('We cannot cancel this order.'));
        }
        return $this;
    }

    /**
     * Retrieve tracking numbers
     *
     * @return array
     */
    public function getTrackingNumbers()
    {
        if ($this->getData('tracking_numbers')) {
            return explode(',', $this->getData('tracking_numbers'));
        }
        return array();
    }

    /**
     * Retrieve shipping method
     *
     * @param bool $asObject return carrier code and shipping method data as object
     * @return string|\Magento\Framework\Object
     */
    public function getShippingMethod($asObject = false)
    {
        $shippingMethod = parent::getShippingMethod();
        if (!$asObject) {
            return $shippingMethod;
        } else {
            list($carrierCode, $method) = explode('_', $shippingMethod, 2);
            return new \Magento\Framework\Object(array('carrier_code' => $carrierCode, 'method' => $method));
        }
    }

    /*********************** ADDRESSES ***************************/

    /**
     * @return Collection
     */
    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = $this->_addressCollectionFactory->create()->setOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_addresses as $address) {
                    $address->setOrder($this);
                }
            }
        }

        return $this->_addresses;
    }

    /**
     * @param mixed $addressId
     * @return false
     */
    public function getAddressById($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address $address
     * @return $this
     */
    public function addAddress(\Magento\Sales\Model\Order\Address $address)
    {
        $address->setOrder($this)->setParentId($this->getId());
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
            $this->setDataChanges(true);
        }
        return $this;
    }

    /**
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return ImportCollection
     */
    public function getItemsCollection($filterByTypes = array(), $nonChildrenOnly = false)
    {
        if (is_null($this->_items)) {
            $this->_items = $this->_orderItemCollectionFactory->create()->setOrderFilter($this);

            if ($filterByTypes) {
                $this->_items->filterByTypes($filterByTypes);
            }
            if ($nonChildrenOnly) {
                $this->_items->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setOrder($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * Get random items collection with related children
     *
     * @param int $limit
     * @return ImportCollection
     */
    public function getItemsRandomCollection($limit = 1)
    {
        return $this->_getItemsRandomCollection($limit);
    }

    /**
     * Get random items collection without related children
     *
     * @param int $limit
     * @return ImportCollection
     */
    public function getParentItemsRandomCollection($limit = 1)
    {
        return $this->_getItemsRandomCollection($limit, true);
    }

    /**
     * Get random items collection with or without related children
     *
     * @param int $limit
     * @param bool $nonChildrenOnly
     * @return ImportCollection
     */
    protected function _getItemsRandomCollection($limit, $nonChildrenOnly = false)
    {
        $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($this)->setRandomOrder();

        if ($nonChildrenOnly) {
            $collection->filterByParent();
        }
        $products = array();
        foreach ($collection as $item) {
            $products[] = $item->getProductId();
        }

        $productsCollection = $this->_productFactory->create()->getCollection()->addIdFilter(
            $products
        )->setVisibility(
            $this->_productVisibility->getVisibleInSiteIds()
        )->addPriceData()->setPageSize(
            $limit
        )->load();

        foreach ($collection as $item) {
            $product = $productsCollection->getItemById($item->getProductId());
            if ($product) {
                $item->setProduct($product);
            }
        }

        return $collection;
    }

    /**
     * @return \Magento\Sales\Model\Order\Item[]
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public function getAllVisibleItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param mixed $itemId
     * @return \Magento\Framework\Object
     */
    public function getItemById($itemId)
    {
        return $this->getItemsCollection()->getItemById($itemId);
    }

    /**
     * @param mixed $quoteItemId
     * @return  \Magento\Framework\Object|null
     */
    public function getItemByQuoteItemId($quoteItemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getQuoteItemId() == $quoteItemId) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function addItem(\Magento\Sales\Model\Order\Item $item)
    {
        $item->setOrder($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Whether the order has nominal items only
     *
     * @return bool
     */
    public function isNominal()
    {
        foreach ($this->getAllVisibleItems() as $item) {
            if ('0' == $item->getIsNominal()) {
                return false;
            }
        }
        return true;
    }

    /*********************** PAYMENTS ***************************/

    /**
     * @return PaymentCollection
     */
    public function getPaymentsCollection()
    {
        if (is_null($this->_payments)) {
            $this->_payments = $this->_paymentCollectionFactory->create()->setOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_payments as $payment) {
                    $payment->setOrder($this);
                }
            }
        }
        return $this->_payments;
    }

    /**
     * @return array
     */
    public function getAllPayments()
    {
        $payments = array();
        foreach ($this->getPaymentsCollection() as $payment) {
            if (!$payment->isDeleted()) {
                $payments[] = $payment;
            }
        }
        return $payments;
    }

    /**
     * @param mixed $paymentId
     * @return Payment|false
     */
    public function getPaymentById($paymentId)
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if ($payment->getId() == $paymentId) {
                return $payment;
            }
        }
        return false;
    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function addPayment(Payment $payment)
    {
        $payment->setOrder($this)->setParentId($this->getId());
        if (!$payment->getId()) {
            $this->getPaymentsCollection()->addItem($payment);
            $this->setDataChanges(true);
        }
        return $this;
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function setPayment(Payment $payment)
    {
        if (!$this->getIsMultiPayment() && ($old = $this->getPayment())) {
            $payment->setId($old->getId());
        }
        $this->addPayment($payment);
        return $payment;
    }

    /*********************** STATUSES ***************************/
    /**
     * Return collection of order status history items.
     *
     * @param bool $reload
     * @return HistoryCollection
     */
    public function getStatusHistoryCollection($reload = false)
    {
        if (is_null($this->_statusHistory) || $reload) {
            $this->_statusHistory = $this->_historyCollectionFactory->create()->setOrderFilter(
                $this
            )->setOrder(
                'created_at',
                'desc'
            )->setOrder(
                'entity_id',
                'desc'
            );

            if ($this->getId()) {
                foreach ($this->_statusHistory as $status) {
                    $status->setOrder($this);
                }
            }
        }
        return $this->_statusHistory;
    }

    /**
     * Return array of order status history items without deleted.
     *
     * @return array
     */
    public function getAllStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted()) {
                $history[] = $status;
            }
        }
        return $history;
    }

    /**
     * Return collection of visible on frontend order status history items.
     *
     * @return array
     */
    public function getVisibleStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted() && $status->getComment() && $status->getIsVisibleOnFront()) {
                $history[] = $status;
            }
        }
        return $history;
    }

    /**
     * @param mixed $statusId
     * @return string|false
     */
    public function getStatusHistoryById($statusId)
    {
        foreach ($this->getStatusHistoryCollection() as $status) {
            if ($status->getId() == $statusId) {
                return $status;
            }
        }
        return false;
    }

    /**
     * Set the order status history object and the order object to each other
     * Adds the object to the status history collection, which is automatically saved when the order is saved.
     * See the entity_id attribute backend model.
     * Or the history record can be saved standalone after this.
     *
     * @param \Magento\Sales\Model\Order\Status\History $history
     * @return $this
     */
    public function addStatusHistory(\Magento\Sales\Model\Order\Status\History $history)
    {
        $history->setOrder($this);
        $this->setStatus($history->getStatus());
        if (!$history->getId()) {
            $this->getStatusHistoryCollection()->addItem($history);
            $this->setDataChanges(true);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getRealOrderId()
    {
        $id = $this->getData('real_order_id');
        if (is_null($id)) {
            $id = $this->getIncrementId();
        }
        return $id;
    }

    /**
     * Get currency model instance. Will be used currency with which order placed
     *
     * @return Currency
     */
    public function getOrderCurrency()
    {
        if (is_null($this->_orderCurrency)) {
            $this->_orderCurrency = $this->_currencyFactory->create();
            $this->_orderCurrency->load($this->getOrderCurrencyCode());
        }
        return $this->_orderCurrency;
    }

    /**
     * Get formatted price value including order currency rate to order website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getOrderCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    /**
     * Retrieve text formatted price value including order rate
     *
     * @param   float $price
     * @return  string
     */
    public function formatPriceTxt($price)
    {
        return $this->getOrderCurrency()->formatTxt($price);
    }

    /**
     * Retrieve order website currency for working with base prices
     *
     * @return Currency
     */
    public function getBaseCurrency()
    {
        if (is_null($this->_baseCurrency)) {
            $this->_baseCurrency = $this->_currencyFactory->create()->load($this->getBaseCurrencyCode());
        }
        return $this->_baseCurrency;
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatBasePrice($price)
    {
        return $this->formatBasePricePrecision($price, 2);
    }

    /**
     * @param float $price
     * @param int $precision
     * @return string
     */
    public function formatBasePricePrecision($price, $precision)
    {
        return $this->getBaseCurrency()->formatPrecision($price, $precision);
    }

    /**
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        return $this->getOrderCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Retrieve order total due value
     *
     * @return float
     */
    public function getTotalDue()
    {
        $total = $this->getGrandTotal() - $this->getTotalPaid();
        $total = $this->priceCurrency->round($total);
        return max($total, 0);
    }

    /**
     * Retrieve order total due value
     *
     * @return float
     */
    public function getBaseTotalDue()
    {
        $total = $this->getBaseGrandTotal() - $this->getBaseTotalPaid();
        $total = $this->priceCurrency->round($total);
        return max($total, 0);
    }

    /**
     * @param string $key
     * @param null|string|int $index
     * @return float
     */
    public function getData($key = '', $index = null)
    {
        if ($key == 'total_due') {
            return $this->getTotalDue();
        }
        if ($key == 'base_total_due') {
            return $this->getBaseTotalDue();
        }
        return parent::getData($key, $index);
    }

    /**
     * Retrieve order invoices collection
     *
     * @return InvoiceCollection
     */
    public function getInvoiceCollection()
    {
        if (is_null($this->_invoices)) {
            $this->_invoices = $this->_invoiceCollectionFactory->create()->setOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_invoices as $invoice) {
                    $invoice->setOrder($this);
                }
            }
        }
        return $this->_invoices;
    }

    /**
     * Retrieve order shipments collection
     *
     * @return ShipmentCollection|false
     */
    public function getShipmentsCollection()
    {
        if (empty($this->_shipments)) {
            if ($this->getId()) {
                $this->_shipments = $this->_shipmentCollectionFactory->create()->setOrderFilter($this)->load();
            } else {
                return false;
            }
        }
        return $this->_shipments;
    }

    /**
     * Retrieve order creditmemos collection
     *
     * @return CreditmemoCollection|false
     */
    public function getCreditmemosCollection()
    {
        if (empty($this->_creditmemos)) {
            if ($this->getId()) {
                $this->_creditmemos = $this->_memoCollectionFactory->create()->setOrderFilter($this)->load();
            } else {
                return false;
            }
        }
        return $this->_creditmemos;
    }

    /**
     * Retrieve order tracking numbers collection
     *
     * @return TrackCollection
     */
    public function getTracksCollection()
    {
        if (empty($this->_tracks)) {
            $this->_tracks = $this->_trackCollectionFactory->create()->setOrderFilter($this);

            if ($this->getId()) {
                $this->_tracks->load();
            }
        }
        return $this->_tracks;
    }

    /**
     * Check order invoices availability
     *
     * @return bool
     */
    public function hasInvoices()
    {
        return $this->getInvoiceCollection()->count();
    }

    /**
     * Check order shipments availability
     *
     * @return bool
     */
    public function hasShipments()
    {
        return $this->getShipmentsCollection()->count();
    }

    /**
     * Check order creditmemos availability
     *
     * @return bool
     */
    public function hasCreditmemos()
    {
        return $this->getCreditmemosCollection()->count();
    }

    /**
     * Retrieve array of related objects
     *
     * Used for order saving
     *
     * @return array
     */
    public function getRelatedObjects()
    {
        return $this->_relatedObjects;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        if ($this->getCustomerFirstname()) {
            $customerName = $this->getCustomerFirstname() . ' ' . $this->getCustomerLastname();
        } else {
            $customerName = (string)__('Guest');
        }
        return $customerName;
    }

    /**
     * Add New object to related array
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function addRelatedObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_relatedObjects[] = $object;
        return $this;
    }

    /**
     * Get formated order created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        return $this->_localeDate->formatDate($this->getCreatedAtStoreDate(), $format, true);
    }

    /**
     * @return string
     */
    public function getEmailCustomerNote()
    {
        if ($this->getCustomerNoteNotify()) {
            return $this->getCustomerNote();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getStoreGroupName()
    {
        $storeId = $this->getStoreId();
        if (is_null($storeId)) {
            return $this->getStoreName(1);
        }
        return $this->getStore()->getGroup()->getName();
    }

    /**
     * Resets all data in object
     * so after another load it will be complete new object
     *
     * @return $this
     */
    public function reset()
    {
        $this->unsetData();
        $this->_actionFlag = array();
        $this->_addresses = null;
        $this->_items = null;
        $this->_payments = null;
        $this->_statusHistory = null;
        $this->_invoices = null;
        $this->_tracks = null;
        $this->_shipments = null;
        $this->_creditmemos = null;
        $this->_relatedObjects = array();
        $this->_orderCurrency = null;
        $this->_baseCurrency = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsNotVirtual()
    {
        return !$this->getIsVirtual();
    }

    /**
     * @return array
     */
    public function getFullTaxInfo()
    {
        $rates = $this->_orderTaxCollectionFactory->create()->loadByOrder($this)->toArray();
        return $this->_taxCalculation->reproduceProcess($rates['items']);
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     *
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function prepareInvoice($qtys = array())
    {
        $invoice = $this->_serviceOrderFactory->create(array('order' => $this))->prepareInvoice($qtys);
        return $invoice;
    }

    /**
     * Create new shipment with maximum qty for shipping for each item
     *
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function prepareShipment($qtys = array())
    {
        $shipment = $this->_serviceOrderFactory->create(array('order' => $this))->prepareShipment($qtys);
        return $shipment;
    }

    /**
     * Check whether order is canceled
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getState() === self::STATE_CANCELED;
    }

    /**
     * Returns increment id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }
}
