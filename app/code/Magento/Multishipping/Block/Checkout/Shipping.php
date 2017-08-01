<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Mustishipping checkout shipping
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Shipping extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Filter\DataObject\GridFactory
     * @since 2.0.0
     */
    protected $_filterGridFactory;

    /**
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxHelper;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
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
     * @since 2.0.0
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Shipping Methods') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * @return Address[]
     * @since 2.0.0
     */
    public function getAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getAddressCount()
    {
        $count = $this->getData('address_count');
        if ($count === null) {
            $count = count($this->getAddresses());
            $this->setData('address_count', $count);
        }
        return $count;
    }

    /**
     * @param Address $address
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getAddressItems($address)
    {
        $items = [];
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
     * @since 2.0.0
     */
    public function getAddressShippingMethod($address)
    {
        return $address->getShippingMethod();
    }

    /**
     * @param Address $address
     * @return mixed
     * @since 2.0.0
     */
    public function getShippingRates($address)
    {
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }

    /**
     * @param string $carrierCode
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAddressEditUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getItemsEditUrl()
    {
        return $this->getUrl('*/*/backToAddresses');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/shippingPost');
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Framework\DataObject $addressEntity
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getItemsBoxTextAfter(\Magento\Framework\DataObject $addressEntity)
    {
        return '';
    }
}
