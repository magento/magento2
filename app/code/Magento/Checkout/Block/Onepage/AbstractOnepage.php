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
namespace Magento\Checkout\Block\Onepage;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface as CustomerAddressService;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Directory\Model\Resource\Country\Collection;
use Magento\Directory\Model\Resource\Region\Collection as RegionCollection;
use Magento\Sales\Model\Quote;

/**
 * One page common functionality block
 */
abstract class AbstractOnepage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $_customer;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var  Collection
     */
    protected $_countryCollection;

    /**
     * @var RegionCollection
     */
    protected $_regionCollection;

    /**
     * @var mixed
     */
    protected $_addressesCollection;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Directory\Model\Resource\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @var \Magento\Directory\Model\Resource\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var CustomerAccountService
     */
    protected $_customerAccountService;

    /**
     * @var CustomerAddressService
     */
    protected $_customerAddressService;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $_addressConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerAccountService $customerAccountService
     * @param CustomerAddressService $customerAddressService
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
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
        CustomerAccountService $customerAccountService,
        CustomerAddressService $customerAddressService,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_configCacheType = $configCacheType;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $resourceSession;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->_customerAccountService = $customerAccountService;
        $this->_customerAddressService = $customerAddressService;
        $this->_addressConfig = $addressConfig;
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
     * Get logged in customer
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _getCustomerData()
    {
        if (empty($this->_customer)) {
            $this->_customer = $this->_customerAccountService->getCustomer($this->_customerSession->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH);
    }

    /**
     * @return Collection
     */
    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = $this->_countryCollectionFactory->create()->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * @return RegionCollection
     */
    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = $this->_regionCollectionFactory->create()->addCountryFilter(
                $this->getAddress()->getCountryId()
            )->load();
        }
        return $this->_regionCollection;
    }

    /**
     * @return int
     */
    public function customerHasAddresses()
    {
        try {
            return count($this->_customerAddressService->getAddresses($this->_getCustomerData()->getId()));
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * @param string $type
     * @return string
     */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $customerId = $this->_getCustomerData()->getId();
            $options = array();

            try {
                $addresses = $this->_customerAddressService->getAddresses($customerId);
            } catch (NoSuchEntityException $e) {
                $addresses = array();
            }

            foreach ($addresses as $address) {
                /** @var \Magento\Customer\Service\V1\Data\Address $address */
                $label = $this->_addressConfig->getFormatByCode(
                    AddressConfig::DEFAULT_ADDRESS_FORMAT
                )->getRenderer()->renderArray(
                    \Magento\Customer\Service\V1\Data\AddressConverter::toFlatArray($address)
                );

                $options[] = array('value' => $address->getId(), 'label' => $label);
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                try {
                    if ($type == 'billing') {
                        $address = $this->_customerAddressService->getDefaultBillingAddress($customerId);
                    } else {
                        $address = $this->_customerAddressService->getDefaultShippingAddress($customerId);
                    }
                    if ($address) {
                        $addressId = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    // Do nothing
                }
            }

            $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                //->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                // temp disable inline javascript, need to clean this later
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', __('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = $this->_coreData->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            $type . '[country_id]'
        )->setId(
            $type . ':country_id'
        )->setTitle(
            __('Country')
        )->setClass(
            'validate-select'
        )->setValue(
            $countryId
        )->setOptions(
            $this->getCountryOptions()
        );
        return $select->getHtml();
    }

    /**
     * @param string $type
     * @return string
     */
    public function getRegionHtmlSelect($type)
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            $type . '[region]'
        )->setId(
            $type . ':region'
        )->setTitle(
            __('State/Province')
        )->setClass(
            'required-entry validate-state'
        )->setValue(
            $this->getAddress()->getRegionId()
        )->setOptions(
            $this->getRegionCollection()->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getCountryOptions()
    {
        $options = false;
        $cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        if ($optionsCache = $this->_configCacheType->load($cacheId)) {
            $options = unserialize($optionsCache);
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            $this->_configCacheType->save(serialize($options), $cacheId);
        }
        return $options;
    }

    /**
     * Get checkout steps codes
     *
     * @return string[]
     */
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
    }

    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return true;
    }

    /**
     * Return the html text for shipping price
     *
     * @param \Magento\Sales\Model\Quote\Address\Rate $rate
     * @return string
     */
    public function getShippingPriceHtml(\Magento\Sales\Model\Quote\Address\Rate $rate)
    {
        /** @var \Magento\Checkout\Block\Shipping\Price $block */
        $block = $this->getLayout()->getBlock('checkout.shipping.price');
        $block->setShippingRate($rate);
        return $block->toHtml();
    }
}
