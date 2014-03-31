<?php
/**
 * Customer dashboard addresses section
 *
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
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\AddressConverter;

class Address extends \Magento\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var \Magento\Customer\Service\V1\CustomerCurrentServiceInterface
     */
    protected $customerCurrentService;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressCurrentServiceInterface
     */
    protected $customerAddressCurrentService;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Customer\Service\V1\CustomerCurrentServiceInterface $customerCurrentService
     * @param \Magento\Customer\Service\V1\CustomerAddressCurrentServiceInterface $customerAddressCurrentService
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Customer\Service\V1\CustomerCurrentServiceInterface $customerCurrentService,
        \Magento\Customer\Service\V1\CustomerAddressCurrentServiceInterface $customerAddressCurrentService,
        \Magento\Customer\Model\Address\Config $addressConfig,
        array $data = array()
    ) {
        $this->customerCurrentService = $customerCurrentService;
        $this->customerAddressCurrentService = $customerAddressCurrentService;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get the logged in customer
     *
     * @return \Magento\Customer\Service\V1\Data\Customer|null
     */
    public function getCustomer()
    {
        try {
            return $this->customerCurrentService->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * HTML for Shipping Address
     *
     * @return string
     */
    public function getPrimaryShippingAddressHtml()
    {
        try {
            $address = $this->customerAddressCurrentService->getDefaultShippingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default shipping address.');
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return __('You have not set a default shipping address.');
        }
    }

    /**
     * HTML for Billing Address
     *
     * @return string
     */
    public function getPrimaryBillingAddressHtml()
    {
        try {
            $address = $this->customerAddressCurrentService->getDefaultBillingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default billing address.');
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return __('You have not set a default billing address.');
        }
    }

    /**
     * @return string
     */
    public function getPrimaryShippingAddressEditUrl()
    {
        if (is_null($this->getCustomer())) {
            return '';
        } else {
            return $this->_urlBuilder->getUrl(
                'customer/address/edit',
                array('id' => $this->getCustomer()->getDefaultShipping())
            );
        }
    }

    /**
     * @return string
     */
    public function getPrimaryBillingAddressEditUrl()
    {
        if (is_null($this->getCustomer())) {
            return '';
        } else {
            return $this->_urlBuilder->getUrl(
                'customer/address/edit',
                array('id' => $this->getCustomer()->getDefaultBilling())
            );
        }
    }

    /**
     * @return string
     */
    public function getAddressBookUrl()
    {
        return $this->getUrl('customer/address/');
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray(AddressConverter::toFlatArray($address));
    }
}
