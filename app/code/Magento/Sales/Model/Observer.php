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
 * Sales observer
 */
namespace Magento\Sales\Model;

class Observer
{
    /**
     * Expire quotes additional fields to filter
     *
     * @var array
     */
    protected $_expireQuotesFilterFields = array();

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_customerAddress;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerData;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\CollectionFactory
     */
    protected $_quoteCollectionFactory;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_coreLocale;

    /**
     * @var Resource\Report\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Report\InvoicedFactory
     */
    protected $_invoicedFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Report\RefundedFactory
     */
    protected $_refundedFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Report\BestsellersFactory
     */
    protected $_bestsellersFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Sales\Model\Resource\Quote\CollectionFactory $quoteFactory
     * @param \Magento\Core\Model\LocaleInterface $coreLocale
     * @param Resource\Report\OrderFactory $orderFactory
     * @param Resource\Report\InvoicedFactory $invoicedFactory
     * @param Resource\Report\RefundedFactory $refundedFactory
     * @param Resource\Report\BestsellersFactory $bestsellersFactory
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Sales\Model\Resource\Quote\CollectionFactory $quoteFactory,
        \Magento\Core\Model\LocaleInterface $coreLocale,
        \Magento\Sales\Model\Resource\Report\OrderFactory $orderFactory,
        \Magento\Sales\Model\Resource\Report\InvoicedFactory $invoicedFactory,
        \Magento\Sales\Model\Resource\Report\RefundedFactory $refundedFactory,
        \Magento\Sales\Model\Resource\Report\BestsellersFactory $bestsellersFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_customerData = $customerData;
        $this->_customerAddress = $customerAddress;
        $this->_catalogData = $catalogData;
        $this->_storeConfig = $storeConfig;
        $this->_quoteCollectionFactory = $quoteFactory;
        $this->_coreLocale = $coreLocale;
        $this->_orderFactory = $orderFactory;
        $this->_invoicedFactory = $invoicedFactory;
        $this->_refundedFactory = $refundedFactory;
        $this->_bestsellersFactory = $bestsellersFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Sales\Model\Observer
     */
    public function cleanExpiredQuotes($schedule)
    {
        $this->_eventManager->dispatch('clear_expired_quotes_before', array('sales_observer' => $this));

        $lifetimes = $this->_storeConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId=>$lifetime) {
            $lifetime *= 86400;

            /** @var $quotes \Magento\Sales\Model\Resource\Quote\Collection */
            $quotes = $this->_quoteCollectionFactory->create();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', array('to'=>date("Y-m-d", time()-$lifetime)));
            $quotes->addFieldToFilter('is_active', 0);

            foreach ($this->getExpireQuotesAdditionalFilterFields() as $field => $condition) {
                $quotes->addFieldToFilter($field, $condition);
            }

            $quotes->walk('delete');
        }
        return $this;
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     */
    public function getExpireQuotesAdditionalFilterFields()
    {
        return $this->_expireQuotesFilterFields;
    }

    /**
     * Set expire quotes additional fields to filter
     *
     * @param array $fields
     * @return \Magento\Sales\Model\Observer
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->_expireQuotesFilterFields = $fields;
        return $this;
    }

    /**
     * Refresh sales order report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Sales\Model\Observer
     */
    public function aggregateSalesReportOrderData($schedule)
    {
        $this->_coreLocale->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_orderFactory->create()->aggregate($date);
        $this->_coreLocale->revert();
        return $this;
    }

    /**
     * Refresh sales invoiced report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Sales\Model\Observer
     */
    public function aggregateSalesReportInvoicedData($schedule)
    {
        $this->_coreLocale->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_invoicedFactory->create()->aggregate($date);
        $this->_coreLocale->revert();
        return $this;
    }

    /**
     * Refresh sales refunded report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Sales\Model\Observer
     */
    public function aggregateSalesReportRefundedData($schedule)
    {
        $this->_coreLocale->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_refundedFactory->create()->aggregate($date);
        $this->_coreLocale->revert();
        return $this;
    }

    /**
     * Refresh bestsellers report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Sales\Model\Observer
     */
    public function aggregateSalesReportBestsellersData($schedule)
    {
        $this->_coreLocale->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_bestsellersFactory->create()->aggregate($date);
        $this->_coreLocale->revert();
        return $this;
    }

    /**
     * Set Quote information about MSRP price enabled
     *
     * @param \Magento\Event\Observer $observer
     */
    public function setQuoteCanApplyMsrp(\Magento\Event\Observer $observer)
    {
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if ($this->_catalogData->isMsrpEnabled()) {
            foreach ($quote->getAllAddresses() as $address) {
                if ($address->getCanApplyMsrp()) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }

        $quote->setCanApplyMsrp($canApplyMsrp);
    }

    /**
     * Add VAT validation request date and identifier to order comments
     *
     * @param \Magento\Event\Observer $observer
     * @return null
     */
    public function addVatRequestParamsOrderComment(\Magento\Event\Observer $observer)
    {
        /** @var $orderInstance \Magento\Sales\Model\Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress \Magento\Sales\Model\Order\Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!($orderAddress instanceof \Magento\Sales\Model\Order\Address)) {
            return;
        }

        $vatRequestId = $orderAddress->getVatRequestId();
        $vatRequestDate = $orderAddress->getVatRequestDate();
        if (is_string($vatRequestId) && !empty($vatRequestId) && is_string($vatRequestDate)
            && !empty($vatRequestDate)
        ) {
            $orderHistoryComment = __('VAT Request Identifier')
                . ': ' . $vatRequestId . '<br />' . __('VAT Request Date')
                . ': ' . $vatRequestDate;
            $orderInstance->addStatusHistoryComment($orderHistoryComment, false);
        }
    }

    /**
     * Retrieve sales address (order or quote) on which tax calculation must be based
     *
     * @param \Magento\Core\Model\AbstractModel $salesModel
     * @param \Magento\Core\Model\Store|string|int|null $store
     * @return \Magento\Customer\Model\Address\AbstractAddress|null
     */
    protected function _getVatRequiredSalesAddress($salesModel, $store = null)
    {
        /** TODO: References to Magento\Customer\Model\Address will be eliminated in scope of MAGETWO-21105 */
        $configAddressType = $this->_customerAddress->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING:
                $requiredAddress = $salesModel->getShippingAddress();
                break;
            default:
                $requiredAddress = $salesModel->getBillingAddress();
                break;
        }
        return $requiredAddress;
    }

    /**
     * Restore initial customer group ID in quote if needed on collect_totals_after event of quote address
     *
     * @param \Magento\Event\Observer $observer
     */
    public function restoreQuoteCustomerGroupId($observer)
    {
        /** TODO: References to Magento\Customer\Model\Address will be eliminated in scope of MAGETWO-21105 */
        $quoteAddress = $observer->getQuoteAddress();
        $configAddressType = $this->_customerAddress->getTaxCalculationAddressType();
        // Restore initial customer group ID in quote only if VAT is calculated based on shipping address
        if ($quoteAddress->hasPrevQuoteCustomerGroupId()
            && $configAddressType == \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING
        ) {
            $quoteAddress->getQuote()->setCustomerGroupId($quoteAddress->getPrevQuoteCustomerGroupId());
            $quoteAddress->unsPrevQuoteCustomerGroupId();
        }
    }
}
