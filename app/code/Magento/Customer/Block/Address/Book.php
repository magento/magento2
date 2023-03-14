<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Block\Address\Grid as AddressesGrid;

/**
 * Customer address book block
 *
 * @api
 * @since 100.0.2
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var AddressesGrid
     */
    private $addressesGrid;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface|null $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     * @param AddressesGrid|null $addressesGrid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository = null,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = [],
        Grid $addressesGrid = null
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->addressesGrid = $addressesGrid ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(AddressesGrid::class);
        parent::__construct($context, $data);
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Customer\Block\Address\Grid::getAddAddressUrl
     */
    public function getAddAddressUrl()
    {
        return $this->addressesGrid->getAddAddressUrl();
    }

    /**
     * Generate and return "Back" URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Customer\Block\Address\Grid::getDeleteUrl
     */
    public function getDeleteUrl()
    {
        return $this->addressesGrid->getDeleteUrl();
    }

    /**
     * Generate and return "Edit Address" URL.
     *
     * Address ID passed in parameters
     *
     * @param int $addressId
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Customer\Block\Address\Grid::getAddressEditUrl
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->addressesGrid->getAddressEditUrl($addressId);
    }

    /**
     * Determines is the address primary (billing or shipping)
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * Get current additional customer addresses
     *
     * Will return array of address interfaces if customer have additional addresses and false in other case.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Customer\Block\Address\Grid::getAdditionalAddresses
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->addressesGrid->getAdditionalAddresses();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        return empty($addresses) ? false : $addresses;
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressHtml(\Magento\Customer\Api\Data\AddressInterface $address = null)
    {
        if ($address !== null) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return '';
    }

    /**
     * Get current customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = null;
        try {
            $customer = $this->currentCustomer->getCustomer();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
        return $customer;
    }

    /**
     * Get customer's default billing address
     *
     * @return int|null
     */
    public function getDefaultBilling()
    {
        $customer = $this->getCustomer();
        if ($customer === null) {
            return null;
        } else {
            return $customer->getDefaultBilling();
        }
    }

    /**
     * Get customer address by ID
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get customer's default shipping address
     *
     * @return int|null
     */
    public function getDefaultShipping()
    {
        $customer = $this->getCustomer();
        if ($customer === null) {
            return null;
        } else {
            return $customer->getDefaultShipping();
        }
    }
}
