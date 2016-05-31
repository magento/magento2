<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * Class Addresses
 * Multishipping checkout choose item addresses block
 */
class Addresses extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Filter\DataObject\GridFactory
     */
    protected $_filterGridFactory;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $_addressConfig;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
        $this->_filterGridFactory = $filterGridFactory;
        $this->_multishipping = $multishipping;
        $this->customerRepository = $customerRepository;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->addressMapper = $addressMapper;
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve multishipping checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Ship to Multiple Addresses') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = $this->getCheckout()->getQuoteShippingAddressesItems();
        /** @var \Magento\Framework\Filter\DataObject\Grid $itemsFilter */
        $itemsFilter = $this->_filterGridFactory->create();
        $itemsFilter->addFilter(new \Magento\Framework\Filter\Sprintf('%d'), 'qty');
        return $itemsFilter->filter($items);
    }

    /**
     * Retrieve HTML for addresses dropdown
     *
     * @param mixed $item
     * @param int $index
     * @return string
     */
    public function getAddressesHtmlSelect($item, $index)
    {
        $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName('ship[' . $index . '][' . $item->getQuoteItemId() . '][address]')
            ->setId('ship_' . $index . '_' . $item->getQuoteItemId() . '_address')
            ->setValue($item->getCustomerAddressId())
            ->setOptions($this->getAddressOptions());

        return $select->getHtml();
    }

    /**
     * Retrieve options for addresses dropdown
     *
     * @return array
     */
    public function getAddressOptions()
    {
        $options = $this->getData('address_options');
        if ($options === null) {
            $options = [];
            $addresses = [];

            try {
                $addresses = $this->customerRepository->getById($this->getCustomerId())->getAddresses();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                /** Customer does not exist */
            }
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            foreach ($addresses as $address) {
                $label = $this->_addressConfig
                    ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
                    ->getRenderer()
                    ->renderArray($this->addressMapper->toFlatArray($address));

                $options[] = [
                    'value' => $address->getId(),
                    'label' => $label,
                ];
            }
            $this->setData('address_options', $options);
        }

        return $options;
    }

    /**
     * Retrieve active customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getCheckout()->getCustomerSession()->getCustomerId();
    }

    /**
     * @param mixed $item
     * @return string
     */
    public function getItemUrl($item)
    {
        return $this->getUrl('catalog/product/view/id/' . $item->getProductId());
    }

    /**
     * @param mixed $item
     * @return string
     */
    public function getItemDeleteUrl($item)
    {
        return $this->getUrl('*/*/removeItem', ['address' => $item->getQuoteAddressId(), 'id' => $item->getId()]);
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/addressesPost');
    }

    /**
     * @return string
     */
    public function getNewAddressUrl()
    {
        return $this->getUrl('*/checkout_address/newShipping');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('checkout/cart/');
    }

    /**
     * @return bool
     */
    public function isContinueDisabled()
    {
        return !$this->getCheckout()->validateMinimumAmount();
    }
}
