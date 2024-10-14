<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\ScopeInterface;

/**
 * Mustishipping checkout shipping
 *
 * @api
 * @since 100.0.2
 */
class Shipping extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Filter\DataObject\GridFactory
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
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    private $_multishipping;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
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
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * Add page title and prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Shipping Methods') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieves addresses
     *
     * @return Address[]
     */
    public function getAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * Returns count of addresses
     *
     * @return mixed
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
     * Retrieves the address items
     *
     * @param Address $address
     * @return \Magento\Framework\DataObject[]
     */
    public function getAddressItems($address)
    {
        $items = [];
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId() || !$item->getQuoteItemId()) {
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
     * Retrieves the address shipping method
     *
     * @param Address $address
     * @return mixed
     */
    public function getAddressShippingMethod($address)
    {
        return $address->getShippingMethod();
    }

    /**
     * Retrieves address shipping rates
     *
     * @param Address $address
     * @return mixed
     */
    public function getShippingRates($address)
    {
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }

    /**
     * Retrieves the carrier name by the code
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        $name = $this->_scopeConfig->getValue('carriers/' . $carrierCode . '/title', ScopeInterface::SCOPE_STORE);
        return $name ?: $carrierCode;
    }

    /**
     * Retrieves the address edit url
     *
     * @param Address $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * Retrieves the url for items edition
     *
     * @return string
     */
    public function getItemsEditUrl()
    {
        return $this->getUrl('*/*/backToAddresses');
    }

    /**
     * Retrieves the url for the post action
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/shippingPost');
    }

    /**
     * Retrieves the back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoaddresses');
    }

    /**
     * Returns converted and formatted price
     *
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
     * Retrieves text for items box
     *
     * @param \Magento\Framework\DataObject $addressEntity
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItemsBoxTextAfter(\Magento\Framework\DataObject $addressEntity)
    {
        return '';
    }
}
