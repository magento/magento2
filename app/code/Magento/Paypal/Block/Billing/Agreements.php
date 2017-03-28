<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Billing;

/**
 * Customer account billing agreements block
 */
class Agreements extends \Magento\Framework\View\Element\Template
{
    /**
     * Payment methods array
     *
     * @var array
     */
    protected $_paymentMethods = [];

    /**
     * Billing agreements collection
     *
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection
     */
    protected $_billingAgreements = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory
     */
    protected $_agreementCollection;

    /**
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $agreementCollection
     * @param \Magento\Paypal\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $agreementCollection,
        \Magento\Paypal\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_agreementCollection = $agreementCollection;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Set Billing Agreement instance
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class
        )->setCollection(
            $this->getBillingAgreements()
        )->setIsOutputRequired(
            false
        );
        $this->setChild('pager', $pager)->setBackUrl($this->getUrl('customer/account/'));
        $this->getBillingAgreements()->load();
        return $this;
    }

    /**
     * Retrieve billing agreements collection
     *
     * @return \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection
     */
    public function getBillingAgreements()
    {
        if ($this->_billingAgreements === null) {
            $this->_billingAgreements = $this->_agreementCollection->create()->addFieldToFilter(
                'customer_id',
                $this->_customerSession->getCustomerId()
            )->setOrder(
                'agreement_id',
                'desc'
            );
        }
        return $this->_billingAgreements;
    }

    /**
     * Retrieve item value by key
     *
     * @param \Magento\Framework\DataObject|\Magento\Paypal\Model\Billing\Agreement $item
     * @param string $key
     * @return string
     */
    public function getItemValue(\Magento\Paypal\Model\Billing\Agreement $item, $key)
    {
        switch ($key) {
            case 'created_at':
            case 'updated_at':
                $value = $item->getData($key)
                    ? $this->formatDate($item->getData($key), \IntlDateFormatter::SHORT, true)
                    : __('N/A');
                break;
            case 'edit_url':
                $value = $this->getUrl('paypal/billing_agreement/view', ['agreement' => $item->getAgreementId()]);
                break;
            case 'payment_method_label':
                $label = $item->getAgreementLabel();
                $value = $label ? $label : __('N/A');
                break;
            case 'status':
                $value = $item->getStatusLabel();
                break;
            default:
                $value = $item->getData($key) ? $item->getData($key) : __('N/A');
                break;
        }
        return $this->escapeHtml($value);
    }

    /**
     * Load available billing agreement methods
     *
     * @return array
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
     * Retrieve wizard payment options array
     *
     * @return array
     */
    public function getWizardPaymentMethodOptions()
    {
        $paymentMethodOptions = [];
        foreach ($this->_helper->getBillingAgreementMethods() as $paymentMethod) {
            if ($paymentMethod->getConfigData('allow_billing_agreement_wizard') == 1) {
                $paymentMethodOptions[$paymentMethod->getCode()] = $paymentMethod->getTitle();
            }
        }
        return $paymentMethodOptions;
    }

    /**
     * Set data to block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setCreateUrl($this->getUrl('paypal/billing_agreement/startWizard'));
        return parent::_toHtml();
    }
}
