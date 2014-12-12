<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Block\Checkout\Address;

use Magento\Customer\Helper\Address as CustomerAddressHelper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Select
 * Multishipping checkout select billing address
 */
class Select extends \Magento\Multishipping\Block\Checkout\AbstractMultishipping
{
    /**
     * @var CustomerAddressHelper
     */
    protected $_customerAddressHelper;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var AddressConverter
     */
    protected $addressConverter;

    /**
     * @var bool
     */
    protected $_isScopePrivate = true;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param CustomerAddressHelper $customerAddressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        CustomerAddressHelper $customerAddressHelper,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
        $this->_customerAddressHelper = $customerAddressHelper;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $multishipping, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Change Billing Address') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * Get a list of current customer addresses.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddress()
    {
        $addresses = $this->getData('address_collection');
        if (is_null($addresses)) {
            try {
                $addresses = $this->_multishipping->getCustomer()->getAddresses();
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $this->setData('address_collection', $addresses);
        }
        return $addresses;
    }

    /**
     * Represent customer address in HTML format.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressAsHtml(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $formatTypeRenderer = $this->_customerAddressHelper->getFormatTypeRenderer('html');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return $result;
    }

    /**
     * Check if provided address is default customer billing address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool
     */
    public function isAddressDefaultBilling(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultBilling()->getId();
    }

    /**
     * Check if provided address is default customer shipping address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool
     */
    public function isAddressDefaultShipping(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultShipping()->getId();
    }

    /**
     * Get URL of customer address edit page.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getEditAddressUrl(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $this->getUrl('*/*/editAddress', ['id' => $address->getId()]);
    }

    /**
     * Get URL of page, at which customer billing address can be set.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getSetAddressUrl(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $this->getUrl('*/*/setBilling', ['id' => $address->getId()]);
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
