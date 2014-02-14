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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address book block
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Address;

class Book extends \Magento\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Service\V1\CustomerServiceInterface
     */
    protected $_customerService;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $_addressService;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Service\V1\CustomerServiceInterface $customerService
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Service\V1\CustomerServiceInterface $customerService,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService,
        \Magento\Customer\Model\Address\Config $addressConfig,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerService = $customerService;
        $this->_addressService = $addressService;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')
            ->setTitle(__('Address Book'));

        return parent::_prepareLayout();
    }

    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', array('_secure'=>true));
    }

    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', array('_secure'=>true));
    }

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
        return $this->getUrl('customer/address/edit', array('_secure'=>true, 'id' => $addressId));
    }

    /**
     * @return bool
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * @return \Magento\Customer\Service\V1\Dto\Address[]|bool
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->_addressService->getAddresses($this->_customerSession->getCustomerId());
        } catch (\Magento\Exception\NoSuchEntityException $e) {
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
     * @param \Magento\Customer\Service\V1\Dto\Address $address
     * @return string
     */
    public function getAddressHtml(\Magento\Customer\Service\V1\Dto\Address $address = null)
    {
        if (!is_null($address)) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($address->getAttributes());
        }
        return '';
    }

    /**
     * @return \Magento\Customer\Service\V1\Dto\Customer|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if (is_null($customer)) {
            try {
                $customer = $this->_customerService->getCustomer($this->_customerSession->getCustomerId());
            } catch (\Magento\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * @return int
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
     * @return \Magento\Customer\Service\V1\Dto\Address|null
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->_addressService->getAddressById($addressId);
        } catch (\Magento\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return int
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
