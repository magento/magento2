<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Onepage;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Message\Collection;

/**
 * One page checkout status
 */
class Login extends AbstractOnepage
{
    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutData = null;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Model\Address\Mapper $dataObjectConverter
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Registration $registration,
        array $data = []
    ) {
        $this->registration = $registration;
        $this->customerUrl = $customerUrl;
        $this->_checkoutData = $checkoutData;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context,
            $coreData,
            $configCacheType,
            $customerSession,
            $resourceSession,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $customerRepository,
            $addressConfig,
            $httpContext,
            $addressMapper,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        if (!$this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('login', ['label' => __('Checkout Method'), 'allow' => true]);
        }
        parent::_construct();
    }

    /**
     * Get customer registration
     *
     * @return \Magento\Customer\Model\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Return registration URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->customerUrl->getRegisterUrl();
    }

    /**
     * Return forgot password URL
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->customerUrl->getForgotPasswordUrl();
    }

    /**
     * @return Collection
     */
    public function getMessages()
    {
        return $this->messageManager->getMessages(true);
    }

    /**
     * @return string
     */
    public function getPostAction()
    {
        return $this->getUrl('customer/account/loginPost', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getQuote()->getMethod();
    }

    /**
     * @return mixed
     */
    public function getMethodData()
    {
        return $this->getCheckout()->getMethodData();
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getUrl('*/*');
    }

    /**
     * @return string
     */
    public function getErrorUrl()
    {
        return $this->getUrl('*/*');
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_customerSession->getUsername(true);
    }

    /**
     * Check if guests checkout is allowed
     *
     * @return bool
     */
    public function isAllowedGuestCheckout()
    {
        return $this->_checkoutData->isAllowedGuestCheckout($this->getQuote());
    }
}
