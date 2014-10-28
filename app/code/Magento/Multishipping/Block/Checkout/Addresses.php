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
namespace Magento\Multishipping\Block\Checkout;

use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * Multishipping checkout choose item addresses block
 */
class Addresses extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Filter\Object\GridFactory
     */
    protected $_filterGridFactory;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $_addressConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\Object\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $customerAddressService
     * @param AddressConfig $addressConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\Object\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $customerAddressService,
        AddressConfig $addressConfig,
        array $data = array()
    ) {
        $this->_filterGridFactory = $filterGridFactory;
        $this->_multishipping = $multishipping;
        $this->_customerAddressService = $customerAddressService;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
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
        $this->pageConfig->setTitle(__('Ship to Multiple Addresses') . ' - ' . $this->pageConfig->getDefaultTitle());
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = $this->getCheckout()->getQuoteShippingAddressesItems();
        /** @var \Magento\Framework\Filter\Object\Grid $itemsFilter */
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
            ->setName('ship['.$index.']['.$item->getQuoteItemId().'][address]')
            ->setId('ship_'.$index.'_'.$item->getQuoteItemId().'_address')
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
        if (is_null($options)) {
            $options = [];
            $addresses = [];

            try {
                $addresses = $this->_customerAddressService->getAddresses($this->getCustomerId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                /** Customer does not exist */
            }
            /** @var \Magento\Customer\Service\V1\Data\Address $address */
            foreach ($addresses as $address) {
                $label = $this->_addressConfig
                    ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
                    ->getRenderer()
                    ->renderArray(\Magento\Customer\Service\V1\Data\AddressConverter::toFlatArray($address));

                $options[] = [
                    'value' => $address->getId(),
                    'label' => $label
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
        return $this->getUrl('*/*/removeItem', array('address' => $item->getQuoteAddressId(), 'id' => $item->getId()));
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
