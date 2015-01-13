<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Block\Adminhtml\Items\Price;

use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Weee\Block\Item\Price\Renderer as ItemPriceRenderer;

/**
 * Sales Order items price column renderer
 */
class Renderer extends \Magento\Tax\Block\Adminhtml\Items\Price\Renderer
{
    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer,
        \Magento\Tax\Helper\Data $taxHelper,
        ItemPriceRenderer $itemPriceRenderer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $defaultColumnRenderer,
            $taxHelper,
            $itemPriceRenderer,
            $data
        );
    }

    /**
     * Whether to display weee details together with price
     *
     * @return bool
     */
    public function displayPriceWithWeeeDetails()
    {
        return $this->itemPriceRenderer->displayPriceWithWeeeDetails();
    }

    /**
     * Whether to display final price that include Weee amounts
     *
     * @return bool
     */
    public function displayFinalPrice()
    {
        return $this->itemPriceRenderer->displayFinalPrice();
    }

    /**
     * Return HTML for unit price excl tax
     *
     * @return string
     */
    public function getUnitPriceExclTaxHtml()
    {
        $baseUnitPriceExclTax = $this->itemPriceRenderer->getBaseUnitDisplayPriceExclTax();
        $unitPriceExclTax = $this->itemPriceRenderer->getUnitDisplayPriceExclTax();
        return $this->displayPrices($baseUnitPriceExclTax, $unitPriceExclTax);
    }

    /**
     * Return HTML for row price excl tax
     *
     * @return string
     */
    public function getRowPriceExclTaxHtml()
    {
        $baseRowPriceExclTax = $this->itemPriceRenderer->getBaseRowDisplayPriceExclTax();
        $rowPriceExclTax = $this->itemPriceRenderer->getRowDisplayPriceExclTax();
        return $this->displayPrices($baseRowPriceExclTax, $rowPriceExclTax);
    }

    /**
     * Return HTML for unit price incl tax
     *
     * @return string
     */
    public function getUnitPriceInclTaxHtml()
    {
        $baseUnitPriceInclTax = $this->itemPriceRenderer->getBaseUnitDisplayPriceInclTax();
        $unitPriceInclTax = $this->itemPriceRenderer->getUnitDisplayPriceInclTax();
        return $this->displayPrices($baseUnitPriceInclTax, $unitPriceInclTax);
    }

    /**
     * Return HTML for row price incl tax
     *
     * @return string
     */
    public function getRowPriceInclTaxHtml()
    {
        $baseRowPriceInclTax = $this->itemPriceRenderer->getBaseRowDisplayPriceInclTax();
        $rowPriceInclTax = $this->itemPriceRenderer->getRowDisplayPriceInclTax();
        return $this->displayPrices($baseRowPriceInclTax, $rowPriceInclTax);
    }

    /**
     * Return HTML for final unit price excl tax
     *
     * @return string
     */
    public function getFinalUnitPriceExclTaxHtml()
    {
        $baseUnitPriceExclTax = $this->itemPriceRenderer->getBaseFinalUnitDisplayPriceExclTax();
        $unitPriceExclTax = $this->itemPriceRenderer->getFinalUnitDisplayPriceExclTax();
        return $this->displayPrices($baseUnitPriceExclTax, $unitPriceExclTax);
    }

    /**
     * Return HTML for final row price excl tax
     *
     * @return string
     */
    public function getFinalRowPriceExclTaxHtml()
    {
        $baseRowPriceExclTax = $this->itemPriceRenderer->getBaseFinalRowDisplayPriceExclTax();
        $rowPriceExclTax = $this->itemPriceRenderer->getFinalRowDisplayPriceExclTax();
        return $this->displayPrices($baseRowPriceExclTax, $rowPriceExclTax);
    }

    /**
     * Return HTML for final unit price incl tax
     *
     * @return string
     */
    public function getFinalUnitPriceInclTaxHtml()
    {
        $baseUnitPriceInclTax = $this->itemPriceRenderer->getBaseFinalUnitDisplayPriceInclTax();
        $unitPriceInclTax = $this->itemPriceRenderer->getFinalUnitDisplayPriceInclTax();
        return $this->displayPrices($baseUnitPriceInclTax, $unitPriceInclTax);
    }

    /**
     * Return HTML for final row price incl tax
     *
     * @return string
     */
    public function getFinalRowPriceInclTaxHtml()
    {
        $baseRowPriceInclTax = $this->itemPriceRenderer->getBaseFinalRowDisplayPriceInclTax();
        $rowPriceInclTax = $this->itemPriceRenderer->getFinalRowDisplayPriceInclTax();
        return $this->displayPrices($baseRowPriceInclTax, $rowPriceInclTax);
    }

    /**
     * Calculate total amount for the item
     *
     * @param Item|QuoteItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
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
     */
    public function getBaseTotalAmount($item)
    {
        return $this->itemPriceRenderer->getBaseTotalAmount($item);
    }
}
