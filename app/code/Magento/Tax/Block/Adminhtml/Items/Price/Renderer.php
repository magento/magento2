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
namespace Magento\Tax\Block\Adminhtml\Items\Price;

use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Block\Item\Price\Renderer as ItemPriceRenderer;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

/**
 * Sales Order items price column renderer
 */
class Renderer extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
     */
    protected $defaultColumnRenderer;

    /**
     * @var Item|QuoteItem|InvoiceItem|CreditmemoItem
     */
    protected $item;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer
     * @param TaxHelper $taxHelper
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $defaultColumnRenderer,
        TaxHelper $taxHelper,
        ItemPriceRenderer $itemPriceRenderer,
        array $data = array()
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
        return $this->itemPriceRenderer->displayPriceInclTax();
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     */
    public function displayPriceExclTax()
    {
        return $this->itemPriceRenderer->displayPriceExclTax();
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     *
     * @return bool
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

    /**
     * Retrieve formated price, use different formatter depending on type of item
     *
     * @param float $price
     * @return string
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
     */
    public function displayPrices($basePrice, $displayPrice)
    {
        return $this->defaultColumnRenderer->displayPrices($basePrice, $displayPrice);
    }
}
