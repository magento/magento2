<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Billing\Agreement;

/**
 * Customer account billing agreement view block
 *
 * @api
 * @since 2.0.0
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Payment methods array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_paymentMethods = [];

    /**
     * Billing Agreement instance
     *
     * @var \Magento\Paypal\Model\Billing\Agreement
     * @since 2.0.0
     */
    protected $_billingAgreementInstance = null;

    /**
     * Related orders collection
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     * @since 2.0.0
     */
    protected $_relatedOrders = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @since 2.0.0
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     * @since 2.0.0
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Paypal\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement
     * @since 2.0.0
     */
    protected $_agreementResource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Paypal\Helper\Data $helper
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement $agreementResource
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Paypal\Helper\Data $helper,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement $agreementResource,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_coreRegistry = $registry;
        $this->_agreementResource = $agreementResource;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve related orders collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @since 2.0.0
     */
    public function getRelatedOrders()
    {
        if ($this->_relatedOrders === null) {
            $billingAgreement = $this->_getBillingAgreementInstance();
            $billingAgreementId = $billingAgreement ? $billingAgreement->getAgreementId() : 0;
            $this->_relatedOrders = $this->_orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
                (int)$this->_customerSession->getCustomerId()
            )->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
            $this->_agreementResource->addOrdersFilter($this->_relatedOrders, $billingAgreementId);
        }
        return $this->_relatedOrders;
    }

    /**
     * Retrieve order item value by key
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $key
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getOrderItemValue(\Magento\Sales\Model\Order $order, $key)
    {
        $escape = true;
        switch ($key) {
            case 'order_increment_id':
                $value = $order->getIncrementId();
                break;
            case 'created_at':
                $value = $this->formatDate($order->getCreatedAt(), \IntlDateFormatter::SHORT, true);
                break;
            case 'shipping_address':
                $value = $order->getShippingAddress() ? $this->escapeHtml(
                    $order->getShippingAddress()->getName()
                ) : __(
                    'N/A'
                );
                break;
            case 'order_total':
                $value = $order->formatPrice($order->getGrandTotal());
                $escape = false;
                break;
            case 'status_label':
                $value = $order->getStatusLabel();
                break;
            case 'view_url':
                $value = $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
                break;
            default:
                $value = $order->getData($key) ? $order->getData($key) : __('N/A');
                break;
        }
        return $escape ? $this->escapeHtml($value) : $value;
    }

    /**
     * Set pager
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class
        )->setCollection(
            $this->getRelatedOrders()
        )->setIsOutputRequired(
            false
        );
        $this->setChild('pager', $pager);
        $this->getRelatedOrders()->load();

        return $this;
    }

    /**
     * Return current billing agreement.
     *
     * @return \Magento\Paypal\Model\Billing\Agreement|null
     * @since 2.0.0
     */
    protected function _getBillingAgreementInstance()
    {
        if ($this->_billingAgreementInstance === null) {
            $this->_billingAgreementInstance = $this->_coreRegistry->registry('current_billing_agreement');
        }
        return $this->_billingAgreementInstance;
    }

    /**
     * Load available billing agreement methods
     *
     * @return array
     * @since 2.0.0
     */
    protected function _loadPaymentMethods()
    {
        if (!$this->_paymentMethods) {
            foreach ($this->_helper->getBillingAgreementMethods() as $paymentMethod) {
                $this->_paymentMethods[$paymentMethod->getCode()] = $paymentMethod->getTitle();
            }
        }
        return $this->_paymentMethods;
    }

    /**
     * Set data to block
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $this->_loadPaymentMethods();
        $this->setBackUrl($this->getUrl('*/billing_agreement/'));
        $billingAgreement = $this->_getBillingAgreementInstance();
        if ($billingAgreement) {
            $this->setReferenceId($billingAgreement->getReferenceId());

            $this->setCanCancel($billingAgreement->canCancel());
            $this->setCancelUrl(
                $this->getUrl(
                    '*/billing_agreement/cancel',
                    ['_current' => true, 'payment_method' => $billingAgreement->getMethodCode()]
                )
            );

            $paymentMethodTitle = $billingAgreement->getAgreementLabel();
            $this->setPaymentMethodTitle($paymentMethodTitle);

            $createdAt = $billingAgreement->getCreatedAt();
            $updatedAt = $billingAgreement->getUpdatedAt();
            $this->setAgreementCreatedAt(
                $createdAt ? $this->formatDate($createdAt, \IntlDateFormatter::SHORT, true) : __('N/A')
            );
            if ($updatedAt) {
                $this->setAgreementUpdatedAt($this->formatDate($updatedAt, \IntlDateFormatter::SHORT, true));
            }
            $this->setAgreementStatus($billingAgreement->getStatusLabel());
        }

        return parent::_toHtml();
    }
}
