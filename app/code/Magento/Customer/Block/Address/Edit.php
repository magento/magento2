<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address edit block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Edit extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
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
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = [],
        AddressMetadataInterface $addressMetadata = null,
        Address $addressHelper = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentCustomer = $currentCustomer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
        $data['directoryHelper'] = $directoryHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->initAddressObject();

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region_id' => isset($postedData['region_id']) ? $postedData['region_id'] : null,
                'region' => $postedData['region'] ?? null,
            ];
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
        }
        $this->precheckRequiredAttributes();
        return $this;
    }

    /**
     * Initialize address object.
     *
     * @return void
     */
    private function initAddressObject()
    {
        // Init address object
        if ($addressId = $this->getRequest()->getParam('id')) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getCustomerId() != $this->_customerSession->getCustomerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->_address->setPrefix($customer->getPrefix());
            $this->_address->setFirstname($customer->getFirstname());
            $this->_address->setMiddlename($customer->getMiddlename());
            $this->_address->setLastname($customer->getLastname());
            $this->_address->setSuffix($customer->getSuffix());
        }
    }

    /**
     * Precheck attributes that may be required in attribute configuration.
     *
     * @return void
     */
    private function precheckRequiredAttributes()
    {
        $precheckAttributes = $this->getData('check_attributes_on_render');
        $requiredAttributesPrechecked = [];
        if (!empty($precheckAttributes) && is_array($precheckAttributes)) {
            foreach ($precheckAttributes as $attributeCode) {
                $attributeMetadata = $this->addressMetadata->getAttributeMetadata($attributeCode);
                if ($attributeMetadata && $attributeMetadata->isRequired()) {
                    $requiredAttributesPrechecked[$attributeCode] = $attributeCode;
                }
            }
        }
        $this->setData('required_attributes_prechecked', $requiredAttributesPrechecked);
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Customer\Block\Widget\Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = __('Edit Address');
        } else {
            $title = __('Add New Address');
        }
        return $title;
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }

    /**
     * Return the Url for saving.
     *
     * @param int $defaultShipping Default shipping flag
     * @param int $defaultBilling Default billing flag
     * @return string
     */
    public function getSaveUrl($defaultShipping = 0, $defaultBilling = 0)
    {
        $queryParams = ['_secure' => true];
        if ($defaultShipping == 1) {
            $queryParams['default_shipping'] = 1;
        } elseif ($defaultBilling == 1) {
            $queryParams['default_billing'] = 1;
        }

        if ($this->getAddress()->isDefaultBilling() && $this->getAddress()->isDefaultShipping()) {
            $postURL = $this->_urlBuilder->getUrl(
                'customer/address/formPost',
                $queryParams
            );
        } else {
            $queryParams['id'] = $this->getAddress()->getId();
            $postURL = $this->_urlBuilder->getUrl(
                'customer/address/formPost',
                $queryParams
            );
        }
        return $postURL;
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? 0 : $region->getRegionId();
    }

    /**
     * Retrieve the number of addresses associated with the customer given a customer Id.
     *
     * @return int
     */
    public function getCustomerAddressCount()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * Determine if the address can be set as the default billing address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    /**
     * Determine if the address can be set as the default shipping address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();
    }

    /**
     * Is the address the default billing address?
     *
     * @return bool
     */
    public function isDefaultBilling()
    {
        return (bool)$this->getAddress()->isDefaultBilling();
    }

    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
        return (bool)$this->getAddress()->isDefaultShipping();
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

    /**
     * Return back button Url, either to customer address or account.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
