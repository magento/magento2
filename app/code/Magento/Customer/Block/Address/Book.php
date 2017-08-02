<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;

/**
 * Customer address book block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     * @since 2.0.0
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     * @since 2.0.0
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     * @since 2.0.0
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->currentCustomer = $currentCustomer;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', ['_secure' => true]);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * @param int $addressId
     * @return string
     * @since 2.0.0
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     * @since 2.0.0
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->customerRepository->getById($this->currentCustomer->getCustomerId())->getAddresses();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array($address->getId(), $primaryAddressIds)) {
                $additional[] = $address;
            }
        }
        return empty($additional) ? false : $additional;
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     * @since 2.0.0
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
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @since 2.0.0
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            try {
                $customer = $this->customerRepository->getById($this->currentCustomer->getCustomerId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * @return int|null
     * @since 2.0.0
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
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @since 2.0.0
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
     * @return int|null
     * @since 2.0.0
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
