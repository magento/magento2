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
namespace Magento\Multishipping\Block\Checkout\Address;

use Magento\Customer\Service\V1\CustomerAddressServiceInterface;
use Magento\Customer\Service\V1\Data\AddressConverter;
use Magento\Customer\Helper\Address as CustomerAddressHelper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Multishipping checkout select billing address
 */
class Select extends \Magento\Multishipping\Block\Checkout\AbstractMultishipping
{
    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * @var CustomerAddressHelper
     */
    protected $_customerAddressHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param CustomerAddressHelper $customerAddressHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        CustomerAddressServiceInterface $customerAddressService,
        CustomerAddressHelper $customerAddressHelper,
        array $data = array()
    ) {
        $this->_customerAddressService = $customerAddressService;
        $this->_customerAddressHelper = $customerAddressHelper;
        parent::__construct($context, $multishipping, $data);
    }

    /**
     * @var bool
     */
    protected $_isScopePrivate = true;

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setTitle(__('Change Billing Address') . ' - ' . $this->pageConfig->getDefaultTitle());
        return parent::_prepareLayout();
    }

    /**
     * Get a list of current customer addresses.
     *
     * @return \Magento\Customer\Service\V1\Data\Address[]
     */
    public function getAddressCollection()
    {
        $addresses = $this->getData('address_collection');
        if (is_null($addresses)) {
            try {
                $addresses = $this->_customerAddressService->getAddresses(
                    $this->_multishipping->getCustomer()->getId()
                );
            } catch (NoSuchEntityException $e) {
                return array();
            }
            $this->setData('address_collection', $addresses);
        }
        return $addresses;
    }

    /**
     * Represent customer address in HTML format.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $addressData
     * @return string
     */
    public function getAddressAsHtml($addressData)
    {
        $formatTypeRenderer = $this->_customerAddressHelper->getFormatTypeRenderer('html');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray(AddressConverter::toFlatArray($addressData));
        }
        return $result;
    }

    /**
     * Check if provided address is default customer billing address.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return bool
     */
    public function isAddressDefaultBilling($address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultBilling();
    }

    /**
     * Check if provided address is default customer shipping address.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return bool
     */
    public function isAddressDefaultShipping($address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultShipping();
    }

    /**
     * Get URL of customer address edit page.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return string
     */
    public function getEditAddressUrl($address)
    {
        return $this->getUrl('*/*/editAddress', array('id' => $address->getId()));
    }

    /**
     * Get URL of page, at which customer billing address can be set.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return string
     */
    public function getSetAddressUrl($address)
    {
        return $this->getUrl('*/*/setBilling', array('id' => $address->getId()));
    }

    /**
     * @return string
     */
    public function getAddNewUrl()
    {
        return $this->getUrl('*/*/newBilling');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/checkout/billing');
    }
}
