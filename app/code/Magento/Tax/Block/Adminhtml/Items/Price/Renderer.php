<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Adminhtml\Items\Price;

use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Tax\Block\Item\Price\Renderer as ItemPriceRenderer;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Sales Order items price column renderer
 *
 * @api
 * @since 2.0.0
 */
class Renderer extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $taxHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     * @since 2.0.0
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
     * @since 2.0.0
     */
    protected $defaultColumnRenderer;

    /**
     * @var Item|QuoteItem|InvoiceItem|CreditmemoItem
     * @since 2.0.0
     */
    protected $item;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer
     * @param TaxHelper $taxHelper
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer,
        TaxHelper $taxHelper,
        ItemPriceRenderer $itemPriceRenderer,
        array $data = []
    ) {
        $this->defaultColumnRenderer = $defaultColumnRenderer;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->itemPriceRenderer->setZone('sales');
        parent::__construct($context, $data);
    }

    /**
     * Set item
     *
     * @param Item|QuoteItem|InvoiceItem|CreditmemoItem $item
     * @return $this
     * @since 2.0.0
     */
    public function setItem($item)
    {
        $this->itemPriceRenderer->setItem($item);
        $this->defaultColumnRenderer->setItem($item);
        $this->item = $item;
        return $this;
    }

    /**
     * Return order item or quote item
     *
     * @return Item|QuoteItem
     * @since 2.0.0
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Return whether display setting is to display price including tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayPriceInclTax()
    {
        return $this->itemPriceRenderer->displayPriceInclTax();
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayPriceExclTax()
    {
        return $this->itemPriceRenderer->displayPriceExclTax();
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayBothPrices()
    {
        return $this->itemPriceRenderer->displayBothPrices();
    }

    /**
     * Calculate total amount for the item
     *
     * @param Item|QuoteItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
     * @since 2.0.0
     */
    public function getTotalAmount($item)
    {
        return $this->itemPriceRenderer->getTotalAmount($item);
    }

    /**
     * Calculate base total amount for the item
     *
     * @param Item|QuoteItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
     * @since 2.0.0
     */
    public function getBaseTotalAmount($item)
    {
        return $this->itemPriceRenderer->getBaseTotalAmount($item);
    }

    /**
     * Retrieve formated price, use different formatter depending on type of item
     *
     * @param float $price
     * @return string
     * @since 2.0.0
     */
    public function formatPrice($price)
    {
        return $this->itemPriceRenderer->formatPrice($price);
    }

    /**
     * Return html that contains both base price and display price
     *
     * @param float $basePrice
     * @param float $displayPrice
     * @return string
     * @since 2.0.0
     */
    public function displayPrices($basePrice, $displayPrice)
    {
        return $this->defaultColumnRenderer->displayPrices($basePrice, $displayPrice);
    }
}
