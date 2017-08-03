<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Billing;

use Magento\Sales\Model\Order\Payment;

/**
 * Billing Agreement abstract model
 *
 * @api
 * @method \Magento\Paypal\Model\ResourceModel\Billing\Agreement _getResource()
 * @method \Magento\Paypal\Model\ResourceModel\Billing\Agreement getResource()
 * @method int getCustomerId()
 * @method \Magento\Paypal\Model\Billing\Agreement setCustomerId(int $value)
 * @method string getMethodCode()
 * @method \Magento\Paypal\Model\Billing\Agreement setMethodCode(string $value)
 * @method string getReferenceId()
 * @method \Magento\Paypal\Model\Billing\Agreement setReferenceId(string $value)
 * @method string getStatus()
 * @method \Magento\Paypal\Model\Billing\Agreement setStatus(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Paypal\Model\Billing\Agreement setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Paypal\Model\Billing\Agreement setUpdatedAt(string $value)
 * @method int getStoreId()
 * @method \Magento\Paypal\Model\Billing\Agreement setStoreId(int $value)
 * @method string getAgreementLabel()
 * @method \Magento\Paypal\Model\Billing\Agreement setAgreementLabel(string $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Agreement extends \Magento\Paypal\Model\Billing\AbstractAgreement
{
    const STATUS_ACTIVE = 'active';

    const STATUS_CANCELED = 'canceled';

    /**
     * Related agreement orders
     *
     * @var array
     * @since 2.0.0
     */
    protected $_relatedOrders = [];

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory
     * @since 2.0.0
     */
    protected $_billingAgreementFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     * @since 2.0.0
     */
    protected $_dateFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $billingAgreementFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $billingAgreementFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentData, $resource, $resourceCollection, $data);
        $this->_billingAgreementFactory = $billingAgreementFactory;
        $this->_dateFactory = $dateFactory;
    }

    /**
     * Init model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Paypal\Model\ResourceModel\Billing\Agreement::class);
    }

    /**
     * Set created_at parameter
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $date = $this->_dateFactory->create()->gmtDate();
        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($date);
        } else {
            $this->setUpdatedAt($date);
        }
        return parent::beforeSave();
    }

    /**
     * Save agreement order relations
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function afterSave()
    {
        if (!empty($this->_relatedOrders)) {
            $this->_saveOrderRelations();
        }
        return parent::afterSave();
    }

    /**
     * Retrieve billing agreement status label
     *
     * @return \Magento\Framework\Phrase|string
     * @since 2.0.0
     */
    public function getStatusLabel()
    {
        switch ($this->getStatus()) {
            case self::STATUS_ACTIVE:
                return __('Active');
            case self::STATUS_CANCELED:
                return __('Canceled');
            default:
                return '';
        }
    }

    /**
     * Initialize token
     *
     * @return string
     * @since 2.0.0
     */
    public function initToken()
    {
        $this->getPaymentMethodInstance()
             ->initBillingAgreementToken($this);

        return $this->getRedirectUrl();
    }

    /**
     * Get billing agreement details
     * Data from response is inside this object
     *
     * @return $this
     * @since 2.0.0
     */
    public function verifyToken()
    {
        $this->getPaymentMethodInstance()
             ->getBillingAgreementTokenInfo($this);

        return $this;
    }

    /**
     * Create billing agreement
     *
     * @return $this
     * @since 2.0.0
     */
    public function place()
    {
        $this->verifyToken();

        $paymentMethodInstance = $this->getPaymentMethodInstance();
        $paymentMethodInstance->placeBillingAgreement($this);

        $this->setCustomerId($this->getCustomerId())
            ->setMethodCode($this->getMethodCode())
            ->setReferenceId($this->getBillingAgreementId())
            ->setStatus(self::STATUS_ACTIVE)
            ->setAgreementLabel($paymentMethodInstance->getTitle())
            ->save();

        return $this;
    }

    /**
     * Cancel billing agreement
     *
     * @return $this
     * @since 2.0.0
     */
    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCELED);
        $this->getPaymentMethodInstance()->updateBillingAgreementStatus($this);
        return $this->save();
    }

    /**
     * Check whether can cancel billing agreement
     *
     * @return bool
     * @since 2.0.0
     */
    public function canCancel()
    {
        return $this->getStatus() != self::STATUS_CANCELED;
    }

    /**
     * Retrieve billing agreement statuses array
     *
     * @return array
     * @since 2.0.0
     */
    public function getStatusesArray()
    {
        return [
            self::STATUS_ACTIVE     => __('Active'),
            self::STATUS_CANCELED   => __('Canceled')
        ];
    }

    /**
     * Validate data
     *
     * @return bool
     * @since 2.0.0
     */
    public function isValid()
    {
        $result = parent::isValid();
        if (!$this->getCustomerId()) {
            $this->_errors[] = __('The customer ID is not set.');
        }
        if (!$this->getStatus()) {
            $this->_errors[] = __('The Billing Agreement status is not set.');
        }
        return $result && empty($this->_errors);
    }

    /**
     * Import payment data to billing agreement
     *
     * $payment->getBillingAgreementData() contains array with following structure :
     *  [billing_agreement_id]  => string
     *  [method_code]           => string
     *
     * @param Payment $payment
     * @return $this
     * @since 2.0.0
     */
    public function importOrderPayment(Payment $payment)
    {
        $baData = $payment->getBillingAgreementData();

        $this->_paymentMethodInstance = (isset($baData['method_code']))
            ? $this->_paymentData->getMethodInstance($baData['method_code'])
            : $payment->getMethodInstance();

        $this->_paymentMethodInstance->setStore($payment->getMethodInstance()->getStore());
        $this->setCustomerId($payment->getOrder()->getCustomerId())
            ->setMethodCode($this->_paymentMethodInstance->getCode())
            ->setReferenceId($baData['billing_agreement_id'])
            ->setStatus(self::STATUS_ACTIVE)
            ->setAgreementLabel($this->_paymentMethodInstance->getTitle());

        return $this;
    }

    /**
     * Retrieve available customer Billing Agreements
     *
     * @param int $customerId
     * @return \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection
     * @since 2.0.0
     */
    public function getAvailableCustomerBillingAgreements($customerId)
    {
        $collection = $this->_billingAgreementFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', self::STATUS_ACTIVE)
            ->setOrder('agreement_id');
        return $collection;
    }

    /**
     * Check whether need to create billing agreement for customer
     *
     * @param int $customerId
     * @return bool
     * @since 2.0.0
     */
    public function needToCreateForCustomer($customerId)
    {
        return $customerId ? count($this->getAvailableCustomerBillingAgreements($customerId)) == 0 : false;
    }

    /**
     * Add order relation to current billing agreement
     *
     * @param int|\Magento\Sales\Model\Order $orderId
     * @return $this
     * @since 2.0.0
     */
    public function addOrderRelation($orderId)
    {
        $this->_relatedOrders[] = $orderId;
        return $this;
    }

    /**
     * Save related orders
     *
     * @return void
     * @since 2.0.0
     */
    protected function _saveOrderRelations()
    {
        foreach ($this->_relatedOrders as $order) {
            $orderId = $order instanceof \Magento\Sales\Model\Order ? $order->getId() : (int)$order;
            $this->getResource()->addOrderRelation($this->getId(), $orderId);
        }
    }
}
