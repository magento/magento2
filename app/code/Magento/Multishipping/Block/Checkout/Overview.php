<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Block\Checkout;

use Magento\Captcha\Block\Captcha;
use Magento\Checkout\Model\CaptchaPaymentProcessingRateLimiter;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Store\Model\ScopeInterface;

/**
 * Multishipping checkout overview information
 *
 * @api
 * @author Magento Core Team <core@magentocommerce.com>
 * @since  100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $totalsReader;

    /**
     * @param \Magento\Framework\View\Element\Template\Context         $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data                                 $taxHelper
     * @param PriceCurrencyInterface                                   $priceCurrency
     * @param \Magento\Quote\Model\Quote\TotalsCollector               $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader                  $totalsReader
     * @param array                                                    $data
     * @param CheckoutHelper|null                                      $checkoutHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        array $data = [],
        ?CheckoutHelper $checkoutHelper = null
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_multishipping = $multishipping;
        $this->priceCurrency = $priceCurrency;
        $data['taxHelper'] = $this->_taxHelper;
        $data['checkoutHelper'] = $checkoutHelper ?? ObjectManager::getInstance()->get(CheckoutHelper::class);
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->totalsCollector = $totalsCollector;
        $this->totalsReader = $totalsReader;
    }

    /**
     * Overwrite the total value of shipping amount for viewing purpose
     *
     * @param  array $totals
     * @return mixed
     * @throws \Exception
     */
    private function getMultishippingTotals($totals)
    {
        if (isset($totals['shipping']) && !empty($totals['shipping'])) {
            $total = $totals['shipping'];
            $shippingMethod = $total->getAddress()->getShippingMethod();
            if (isset($shippingMethod) && !empty($shippingMethod)) {
                $shippingRate = $total->getAddress()->getShippingRateByCode($shippingMethod);
                $shippingPrice = $shippingRate->getPrice();
            } else {
                $shippingPrice = $total->getAddress()->getShippingAmount();
            }
            /**
             * @var \Magento\Store\Api\Data\StoreInterface
             */
            $store = $this->getQuote()->getStore();
            $amountPrice = $store->getBaseCurrency()
                ->convert($shippingPrice, $store->getCurrentCurrencyCode());
            $total->setBaseShippingAmount($shippingPrice);
            $total->setShippingAmount($amountPrice);
            $total->setValue($amountPrice);
        }
        return $totals;
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
        if (!$this->getChildBlock('captcha')) {
            $this->addChild(
                'captcha',
                Captcha::class,
                [
                    'cacheable' => false,
                    'after' => '-',
                    'form_id' => CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM,
                    'image_width' => 230,
                    'image_height' => 230
                ]
            );
        }

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
     * Get billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->getCheckout()->getQuote()->getBillingAddress();
    }

    /**
     * Get payment info
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Get object with payment info posted data
     *
     * @return \Magento\Framework\DataObject
     */
    public function getPayment()
    {
        return $this->getCheckout()->getQuote()->getPayment();
    }

    /**
     * Get shipping addresses
     *
     * @return array
     */
    public function getShippingAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * Get number of shipping addresses
     *
     * @return int|mixed
     */
    public function getShippingAddressCount()
    {
        $count = $this->getData('shipping_address_count');
        if ($count === null) {
            $count = count($this->getShippingAddresses());
            $this->setData('shipping_address_count', $count);
        }
        return $count;
    }

    /**
     * Get shipping address rate
     *
     * @param                                        Address $address
     * @return                                       bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * Get shipping price including tax
     *
     * @param  Address $address
     * @return mixed
     */
    public function getShippingPriceInclTax($address)
    {
        $rate = $address->getShippingRateByCode($address->getShippingMethod());
        $exclTax = $rate->getPrice();
        $taxAmount = $address->getShippingTaxAmount();
        return $this->formatPrice($exclTax + $taxAmount);
    }

    /**
     * Get shipping price excluding tax
     *
     * @param  Address $address
     * @return mixed
     */
    public function getShippingPriceExclTax($address)
    {
        $rate = $address->getShippingRateByCode($address->getShippingMethod());
        $shippingAmount = $rate->getPrice();
        return $this->formatPrice($shippingAmount);
    }

    /**
     * Format price
     *
     * @param  float $price
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
     * Get shipping address items
     *
     * @param  Address $address
     * @return array
     */
    public function getShippingAddressItems($address): array
    {
        return $address->getAllVisibleItems();
    }

    /**
     * Get shipping address totals
     *
     * @param  Address $address
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
     * Get total price
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->getCheckout()->getQuote()->getGrandTotal();
    }

    /**
     * Get the Edit addresses URL
     *
     * @return string
     */
    public function getAddressesEditUrl()
    {
        return $this->getUrl('*/*/backtoaddresses');
    }

    /**
     * Get the Edit shipping address URL
     *
     * @param  Address $address
     * @return string
     */
    public function getEditShippingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * Get the Edit billing address URL
     *
     * @param  Address $address
     * @return string
     */
    public function getEditBillingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editBilling', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * Get the Edit shipping URL
     *
     * @return string
     */
    public function getEditShippingUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }

    /**
     * Get Post ACtion URL
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/overviewPost');
    }

    /**
     * Get the Edit billing URL
     *
     * @return string
     */
    public function getEditBillingUrl()
    {
        return $this->getUrl('*/*/backtobilling');
    }

    /**
     * Get back button URL
     *
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
        return $this->getUrl('checkout/cart');
    }

    /**
     * Retrieve virtual product collection array
     *
     * @return array
     */
    public function getVirtualItems()
    {
        return $this->getBillingAddress()->getAllVisibleItems();
    }

    /**
     * Retrieve quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Get billin address totals
     *
     * @return     mixed
     * @deprecated 100.2.3
     * typo in method name, see getBillingAddressTotals()
     */
    public function getBillinAddressTotals()
    {
        return $this->getBillingAddressTotals();
    }

    /**
     * Get billing address totals
     *
     * @return mixed
     * @since 100.2.3
     */
    public function getBillingAddressTotals()
    {
        $address = $this->getQuote()->getBillingAddress();
        return $this->getShippingAddressTotals($address);
    }

    /**
     * Render total block
     *
     * @param  mixed $totals
     * @param  null  $colspan
     * @return string
     */
    public function renderTotals($totals, $colspan = null)
    {
        // check if the shipment is multi shipment
        $totals = $this->getMultishippingTotals($totals);

        // sort totals by configuration settings
        $totals = $this->sortTotals($totals);

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
     * @param  \Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowItemHtml(\Magento\Framework\DataObject $item)
    {
        $type = $this->_getItemType($item);
        $renderer = $this->_getRowItemRenderer($type)->setItem($item);
        $this->_prepareItem($renderer);
        return $renderer->toHtml();
    }

    /**
     * Retrieve renderer block for row-level item output
     *
     * @param  string $type
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

    /**
     * Sort total information based on configuration settings.
     *
     * @param array $totals
     * @return array
     */
    private function sortTotals($totals): array
    {
        $sortedTotals = [];
        $sorts = $this->_scopeConfig->getValue(
            Collector::XML_PATH_SALES_TOTALS_SORT,
            ScopeInterface::SCOPE_STORES
        );

        $sorted = [];
        foreach ($sorts as $code => $sortOrder) {
            $sorted[$sortOrder] = $code;
        }
        ksort($sorted);

        foreach ($sorted as $code) {
            if (isset($totals[$code])) {
                $sortedTotals[$code] = $totals[$code];
            }
        }

        $notSorted = array_diff(array_keys($totals), array_keys($sortedTotals));
        foreach ($notSorted as $code) {
            $sortedTotals[$code] = $totals[$code];
        }

        return $sortedTotals;
    }
}
