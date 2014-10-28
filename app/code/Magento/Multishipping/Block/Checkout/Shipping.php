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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Quote\Address;

/**
 * Mustishipping checkout shipping
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Shipping extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Filter\Object\GridFactory
     */
    protected $_filterGridFactory;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\Object\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\Object\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_taxHelper = $taxHelper;
        $this->_filterGridFactory = $filterGridFactory;
        $this->_multishipping = $multishipping;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get multishipping checkout model
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
        $this->pageConfig->setTitle(__('Shipping Methods') . ' - ' . $this->pageConfig->getDefaultTitle());
        return parent::_prepareLayout();
    }

    /**
     * @return Address[]
     */
    public function getAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * @return mixed
     */
    public function getAddressCount()
    {
        $count = $this->getData('address_count');
        if (is_null($count)) {
            $count = count($this->getAddresses());
            $this->setData('address_count', $count);
        }
        return $count;
    }

    /**
     * @param Address $address
     * @return \Magento\Framework\Object[]
     */
    public function getAddressItems($address)
    {
        $items = array();
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $item->setQuoteItem($this->getCheckout()->getQuote()->getItemById($item->getQuoteItemId()));
            $items[] = $item;
        }
        $itemsFilter = $this->_filterGridFactory->create();
        $itemsFilter->addFilter(new \Magento\Framework\Filter\Sprintf('%d'), 'qty');
        return $itemsFilter->filter($items);
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getAddressShippingMethod($address)
    {
        return $address->getShippingMethod();
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingRates($address)
    {
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }

    /**
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * @param Address $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', array('id' => $address->getCustomerAddressId()));
    }

    /**
     * @return string
     */
    public function getItemsEditUrl()
    {
        return $this->getUrl('*/*/backToAddresses');
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/shippingPost');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoaddresses');
    }

    /**
     * @param Address $address
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($address, $price, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->_taxHelper->getShippingPrice($price, $flag, $address),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $address->getQuote()->getStore()
        );
    }

    /**
     * Retrieve text for items box
     *
     * @param \Magento\Framework\Object $addressEntity
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItemsBoxTextAfter(\Magento\Framework\Object $addressEntity)
    {
        return '';
    }
}
