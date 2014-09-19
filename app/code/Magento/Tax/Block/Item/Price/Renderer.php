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
namespace Magento\Tax\Block\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Object as MagentoObject;
use Magento\Sales\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Framework\Pricing\Render as PricingRender;

/**
 * Item price render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var QuoteItem|OrderItem|InvoiceItem|CreditMemoItem
     */
    protected $item;

    /**
     * @var string|int|null
     */
    protected $storeId = null;

    /**
     * Set the display area, e.g., cart, sales, etc.
     *
     * @var string
     */
    protected $zone = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param Context $context
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        TaxHelper $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
        if (isset($data['zone'])) {
            $this->zone = $data['zone'];
        }
        parent::__construct($context, $data);
    }

    /**
     * Set item for render
     *
     * @param QuoteItem|OrderItem|InvoiceItem|CreditMemoItem $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;
        $this->storeId = $item->getStoreId();
        return $this;
    }

    /**
     * Get display zone
     *
     * @return string|null
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set display zone
     *
     * @param string $zone
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
    /**
     * Get quote or order item
     *
     * @return CreditMemoItem|InvoiceItem|OrderItem|QuoteItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Return whether display setting is to display price including tax
     *
     * @return bool
     */
    public function displayPriceInclTax()
    {
        switch ($this->zone) {
            case PricingRender::ZONE_CART:
                return $this->taxHelper->displayCartPriceInclTax($this->storeId);
            case PricingRender::ZONE_EMAIL:
            case PricingRender::ZONE_SALES:
                return $this->taxHelper->displaySalesPriceInclTax($this->storeId);
            default:
                return $this->taxHelper->displayCartPriceInclTax($this->storeId);
        }
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     */
    public function displayPriceExclTax()
    {
        switch ($this->zone) {
            case PricingRender::ZONE_CART:
                return $this->taxHelper->displayCartPriceExclTax($this->storeId);
            case PricingRender::ZONE_EMAIL:
            case PricingRender::ZONE_SALES:
                return $this->taxHelper->displaySalesPriceExclTax($this->storeId);
            default:
                return $this->taxHelper->displayCartPriceExclTax($this->storeId);
        }
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        switch ($this->zone) {
            case PricingRender::ZONE_CART:
                return $this->taxHelper->displayCartBothPrices($this->storeId);
            case PricingRender::ZONE_EMAIL:
            case PricingRender::ZONE_SALES:
                return $this->taxHelper->displaySalesBothPrices($this->storeId);
            default:
                return $this->taxHelper->displayCartBothPrices($this->storeId);
        }
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        $item = $this->getItem();
        if ($item instanceof QuoteItem) {
            return $this->priceCurrency->format(
                $price,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $item->getStore()
            );
        } elseif ($item instanceof OrderItem) {
            return $item->getOrder()->formatPrice($price);
        } else {
            return $item->getOrderItem()->getOrder()->formatPrice($price);
        }
    }

    /**
     * Get item price in display currency or order currency depending
     * on item type
     *
     * @return float
     */
    public function getItemDisplayPriceExclTax()
    {
        $item = $this->getItem();
        if ($item instanceof QuoteItem) {
            return $item->getCalculationPrice();
        } else {
            return $item->getPrice();
        }
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal()
            - $item->getDiscountAmount()
            + $item->getTaxAmount()
            + $item->getHiddenTaxAmount();

        return $totalAmount;
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        $totalAmount = $item->getBaseRowTotal()
            - $item->getBaseDiscountAmount()
            + $item->getBaseTaxAmount()
            + $item->getBaseHiddenTaxAmount();

        return $totalAmount;
    }
}
