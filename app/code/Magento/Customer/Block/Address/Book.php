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
namespace Magento\Customer\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Customer\Model\Address\Mapper;

/**
 * Customer address book block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = array()
    ) {
        $this->customerRepository = $customerRepository;
        $this->currentCustomer = $currentCustomer;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setTitle(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', array('_secure' => true));
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', array('_secure' => true));
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * @param int $addressId
     * @return string
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('customer/address/edit', array('_secure' => true, 'id' => $addressId));
    }

    /**
     * @return bool
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->customerRepository->getById($this->currentCustomer->getCustomerId())->getAddresses();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $primaryAddressIds = array($this->getDefaultBilling(), $this->getDefaultShipping());
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
     */
    public function getAddressHtml(\Magento\Customer\Api\Data\AddressInterface $address = null)
    {
        if (!is_null($address)) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return '';
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if (is_null($customer)) {
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
     */
    public function getDefaultBilling()
    {
        $customer = $this->getCustomer();
        if (is_null($customer)) {
            return null;
        } else {
            return $customer->getDefaultBilling();
        }
    }

    /**
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
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
     */
    public function getDefaultShipping()
    {
        $customer = $this->getCustomer();
        if (is_null($customer)) {
            return null;
        } else {
            return $customer->getDefaultShipping();
        }
    }
}
