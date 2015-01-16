<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Quote\Address;

/**
 * Multishipping checkout overview information
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Overview extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

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
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_multishipping = $multishipping;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize default item renderer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Review Order - %1', $this->pageConfig->getTitle()->getDefault())
        );
        return parent::_prepareLayout();
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
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->getCheckout()->getQuote()->getBillingAddress();
    }

    /**
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Get object with payment info posted data
     *
     * @return \Magento\Framework\Object
     */
    public function getPayment()
    {
        if (!$this->hasData('payment')) {
            $payment = new \Magento\Framework\Object($this->getRequest()->getPost('payment'));
            $this->setData('payment', $payment);
        }
        return $this->_getData('payment');
    }

    /**
     * @return array
     */
    public function getShippingAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * @return int|mixed
     */
    public function getShippingAddressCount()
    {
        $count = $this->getData('shipping_address_count');
        if (is_null($count)) {
            $count = count($this->getShippingAddresses());
            $this->setData('shipping_address_count', $count);
        }
        return $count;
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function getShippingAddressRate($address)
    {
        $rate = $address->getShippingRateByCode($address->getShippingMethod());
        if ($rate) {
            return $rate;
        }
        return false;
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingPriceInclTax($address)
    {
        $exclTax = $address->getShippingAmount();
        $taxAmount = $address->getShippingTaxAmount();
        return $this->formatPrice($exclTax + $taxAmount);
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingPriceExclTax($address)
    {
        return $this->formatPrice($address->getShippingAmount());
    }

    /**
     * @param float $price
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingAddressItems($address)
    {
        return $address->getAllVisibleItems();
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingAddressTotals($address)
    {
        $totals = $address->getTotals();
        foreach ($totals as $total) {
            if ($total->getCode() == 'grand_total') {
                if ($address->getAddressType() == Address::TYPE_BILLING) {
                    $total->setTitle(__('Total'));
                } else {
                    $total->setTitle(__('Total for this address'));
                }
            }
        }
        return $totals;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->getCheckout()->getQuote()->getGrandTotal();
    }

    /**
     * @return string
     */
    public function getAddressesEditUrl()
    {
        return $this->getUrl('*/*/backtoaddresses');
    }

    /**
     * @param Address $address
     * @return string
     */
    public function getEditShippingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * @param Address $address
     * @return string
     */
    public function getEditBillingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editBilling', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * @return string
     */
    public function getEditShippingUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/overviewPost');
    }

    /**
     * @return string
     */
    public function getEditBillingUrl()
    {
        return $this->getUrl('*/*/backtobilling');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtobilling');
    }

    /**
     * Retrieve virtual product edit url
     *
     * @return string
     */
    public function getVirtualProductEditUrl()
    {
        return $this->getUrl('*/cart');
    }

    /**
     * Retrieve virtual product collection array
     *
     * @return array
     */
    public function getVirtualItems()
    {
        $items = [];
        foreach ($this->getBillingAddress()->getItemsCollection() as $_item) {
            if ($_item->isDeleted()) {
                continue;
            }
            if ($_item->getProduct()->getIsVirtual() && !$_item->getParentItemId()) {
                $items[] = $_item;
            }
        }
        return $items;
    }

    /**
     * Retrieve quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * @return mixed
     */
    public function getBillinAddressTotals()
    {
        $_address = $this->getQuote()->getBillingAddress();
        return $this->getShippingAddressTotals($_address);
    }

    /**
     * @param mixed $totals
     * @param null $colspan
     * @return string
     */
    public function renderTotals($totals, $colspan = null)
    {
        if ($colspan === null) {
            $colspan = 3;
        }
        $totals = $this->getChildBlock(
            'totals'
        )->setTotals(
            $totals
        )->renderTotals(
            '',
            $colspan
        ) . $this->getChildBlock(
            'totals'
        )->setTotals(
            $totals
        )->renderTotals(
            'footer',
            $colspan
        );
        return $totals;
    }

    /**
     * Return row-level item html
     *
     * @param \Magento\Framework\Object $item
     * @return string
     */
    public function getRowItemHtml(\Magento\Framework\Object $item)
    {
        $type = $this->_getItemType($item);
        $renderer = $this->_getRowItemRenderer($type)->setItem($item);
        $this->_prepareItem($renderer);
        return $renderer->toHtml();
    }

    /**
     * Retrieve renderer block for row-level item output
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _getRowItemRenderer($type)
    {
        $renderer = $this->getItemRenderer($type);
        if ($renderer !== $this->getItemRenderer(self::DEFAULT_TYPE)) {
            $renderer->setTemplate($this->getRowRendererTemplate());
        }
        return $renderer;
    }
}
