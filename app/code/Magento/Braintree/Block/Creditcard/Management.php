<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Creditcard;

use \Braintree_CreditCard;

class Management extends \Magento\Framework\View\Element\Template
{
    const TYPE_EDIT         = 'edit';

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $vault;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Payment\Model\CcConfig
     */
    protected $ccConfig;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $dataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Braintree\Model\Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Payment\Model\CcConfig $ccConfig
     * @param \Magento\Braintree\Helper\Data $dataHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Braintree\Model\Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Payment\Model\CcConfig $ccConfig,
        \Magento\Braintree\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->paymentConfig = $paymentConfig;
        $this->vault = $vault;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->ccConfig = $ccConfig;
        $this->dataHelper = $dataHelper;
    }


    /**
     * Returns credit card
     *
     * @return \Braintree_CreditCard
     */
    public function creditCard()
    {
        $token = $this->getRequest()->getParam('token');
        return $this->vault->storedCard($token);
    }

    /**
     * Returns page title
     *
     * @return string
     */
    public function getTitle()
    {
        $title = '';
        if ($this->getType() == self::TYPE_EDIT) {
            $title = 'Edit Credit Card';
        } else {
            $title = 'Add Credit Card';
        }
        return __($title);
    }

    /**
     * If card is edited
     * 
     * @return boolean
     */
    public function isEditMode()
    {
        if ($this->getType() == self::TYPE_EDIT) {
            return true;
        }
        return false;
    }

    /**
     * Returns html select for country
     *
     * @param string $name
     * @param string $id
     * @param string $default
     * @param string $title
     * @return string
     */
    public function countrySelect($name, $id, $default = '', $title = 'Country')
    {
        $this->getChildBlock('customer_creditcard_management_country');
        return $this->getChildBlock('customer_creditcard_management_country')
            ->getCountryHtmlSelect($default, $name, $id, $title);
    }

    /**
     * Returns region code by name
     *
     * @param string $region
     * @param string $countryId
     * @return string
     */
    public function getRegionIdByName($region, $countryId)
    {
        $collection = $this->regionCollectionFactory->create()
            ->addRegionCodeOrNameFilter($region)
            ->addCountryFilter($countryId);

        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }
        return '';
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (!($months)) {
            $months[0] =  __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (!($years)) {
            $years = $this->paymentConfig->getYears();
            $years = [ 0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrieve current stored cards
     *
     * @return array
     */
    public function getCurrentCustomerStoredCards()
    {
        return $this->vault->currentCustomerStoredCards();
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomer()
    {
        if ($this->customer == null) {
            $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        }
        return $this->customer;
    }

    /**
     * Customer name
     *
     * @return null|string
     */
    public function currentCustomerName()
    {
        if ($this->config->useVault() && $this->customerSession->isLoggedIn()) {
            return $this->getCustomer()->getFirstname();
        }
        return null;
    }

    /**
     * Customer last name
     *
     * @return null|string
     */
    public function currentCustomerLastName()
    {
        if ($this->config->useVault() && $this->customerSession->isLoggedIn()) {
            return $this->getCustomer()->getLastname();
        }
        return null;
    }

    //@codeCoverageIgnoreStart
    /**
     * Returns url for edit
     *
     * @param string $token
     * @return string
     */
    public function getEditUrl($token)
    {
        return $this->getUrl('braintree/creditcard/edit', ['token' => $token, '_secure' => true]);
    }

    /**
     * Returns url for delete
     *
     * @param string $token
     * @return string
     */
    public function getDeleteUrl($token)
    {
        return $this->getUrl('braintree/creditcard/delete', ['token' => $token, '_secure' => true]);
    }

    /**
     * Returns url for add
     *
     * @return string
     */
    public function getAddUrl()
    {
        return $this->getUrl('braintree/creditcard/newcard', ['_secure' => true]);
    }

    /**
     * Returns url for add
     *
     * @return string
     */
    public function getDeleteConfirmUrl()
    {
        return $this->getUrl('braintree/creditcard/deleteconfirm', ['_secure' => true]);
    }

    /**
     * Returns url for ajax save
     *
     * @return string
     */
    public function getAjaxSaveUrl()
    {
        return $this->getUrl('braintree/creditcard/ajaxsave', ['_secure' => true]);
    }

    /**
     * Returns url for edit form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('braintree/creditcard/save', ['_secure' => true]);
    }

    /**
     * Returns url for edit form
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('braintree/creditcard/index', ['_secure' => true]);
    }

    /**
     * Retrieve use of vault
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUsesVault()
    {
        return $this->config->useVault();
    }

    /**
     * If fraud detection is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isFraudDetectionEnabled()
    {
        return $this->config->isFraudDetectionEnabled();
    }

    /**
     * Retrieve use of vault
     *
     * @return bool
     */
    public function hasVerification()
    {
        return $this->config->useCvv();
    }

    /**
     * Retrieve today month
     *
     * @return string
     */
    public function getTodayMonth()
    {
        return $this->dataHelper->getTodayMonth();
    }

    /**
     * Retrieve today year
     *
     * @return string
     */
    public function getTodayYear()
    {
        return $this->dataHelper->getTodayYear();
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->config->getClientToken();
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return $this->dataHelper->getCcAvailableCardTypes();
    }

    /**
     * Retrieve the cvv image from ccconfig
     *
     * @return string
     */
    public function getCvvImageUrl()
    {
        return $this->ccConfig->getCvvImageUrl();
    }

    /**
     * Retrieve country specific credit card types as json
     *
     * @return string
     */
    public function getCountrySpecificCardTypeConfig()
    {
        return $this->config->getCountrySpecificCardTypeConfig();
    }


    /**
     * Retrieve applicable credit card types
     *
     * @return array
     */
    public function getCcApplicableTypes()
    {
        return $this->config->getApplicableCardTypes(null);
    }

    //@codeCoverageIgnoreStart
}
