<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Onepage checkout block
 */
class Checkout extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Quote\Model\Quote\AddressDataProvider
     */
    protected $addressDataProvider;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface[]
     */
    protected $customLayoutProviders;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Quote\Model\Quote\AddressDataProvider $addressDataProvider
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface[] $customLayoutProviders
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Quote\Model\Quote\AddressDataProvider $addressDataProvider,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        array $customLayoutProviders = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
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
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->addressDataProvider = $addressDataProvider;
        $this->configProvider = $configProvider;
        $this->customLayoutProviders = $customLayoutProviders;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        // The following code is a workaround for custom address attributes
        if (isset($this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children']
        )) {
            $fields = $this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
                ['children']['billing-address-fieldset']['children'];
            $this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
                ['children']['billing-address-fieldset']['children'] = $this->addressDataProvider
                    ->getAdditionalAddressFields('checkoutProvider', 'billingAddress', $fields);
        }
        if (isset($this->jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children']
        )) {
            $fields = $this->jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'];
            $this->jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'] = $this->addressDataProvider
                ->getAdditionalAddressFields('checkoutProvider', 'shippingAddress', $fields);
        }
        foreach ($this->customLayoutProviders as $dataProvider) {
            $customFormLayout = $dataProvider->getData();
            $this->jsLayout = array_merge_recursive($this->jsLayout, $customFormLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get 'one step checkout' step data
     *
     * @return array
     */
    public function getSteps()
    {
        $steps = array();
        $stepCodes = $this->_getStepCodes();

        if ($this->isCustomerLoggedIn()) {
            $stepCodes = array_diff($stepCodes, array('login'));
        }

        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }

        return $steps;
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
        return $this->isCustomerLoggedIn() ? 'billing' : 'login';
    }

    /**
     * Retrieve form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }
}
