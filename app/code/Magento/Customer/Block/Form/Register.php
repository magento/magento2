<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Form;

use Magento\Customer\Model\AccountManagement;
use Magento\Newsletter\Observer\PredispatchNewsletterObserver;

/**
 * Customer register form block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Register extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\Module\ModuleManagerInterface $moduleManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\ModuleManagerInterface $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    ) {
        $this->_customerUrl = $customerUrl;
        $this->_moduleManager = $moduleManager;
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->_isScopePrivate = false;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_customerUrl->getRegisterPostUrl();
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if ($url === null) {
            $url = $this->_customerUrl->getLoginUrl();
        }
        return $url;
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_customerSession->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Retrieve customer country identifier
     *
     * @return int
     */
    public function getCountryId()
    {
        $countryId = $this->getFormData()->getCountryId();
        if ($countryId) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Retrieve customer region identifier
     *
     * @return mixed
     */
    public function getRegion()
    {
        if (null !== ($region = $this->getFormData()->getRegion())) {
            return $region;
        } elseif (null !== ($region = $this->getFormData()->getRegionId())) {
            return $region;
        }
        return null;
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->_moduleManager->isOutputEnabled('Magento_Newsletter')
            && $this->getConfig(PredispatchNewsletterObserver::XML_PATH_NEWSLETTER_ACTIVE);
    }

    /**
     * Restore entity data from session
     *
     * Entity and form code must be defined for the form
     *
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @param string|null $scope
     * @return $this
     */
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $form, $scope = null)
    {
        if ($this->getFormData()->getCustomerData()) {
            $request = $form->prepareRequest($this->getFormData()->getData());
            $data = $form->extractData($request, $scope, false);
            $form->restoreData($data);
        }

        return $this;
    }

    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Get number of password required character classes
     *
     * @return string
     * @since 100.1.0
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }
}
