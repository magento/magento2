<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Block\Total;

/**
 * Nominal total rendered
 *
 * Each item is rendered as separate total with its details
 */
class Nominal extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * Custom template
     *
     * @var string
     */
    protected $_template = 'total/nominal.phtml';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $data);
    }

    /**
     * Getter for a quote item name
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem
     * @return string
     */
    public function getItemName(\Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem)
    {
        return $quoteItem->getName();
    }

    /**
     * Getter for a quote item row total
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem
     * @return float
     */
    public function getItemRowTotal(\Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem)
    {
        return $quoteItem->getNominalRowTotal();
    }

    /**
     * Getter for nominal total item details
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem
     * @return array
     */
    public function getTotalItemDetails(\Magento\Sales\Model\Quote\Item\AbstractItem $quoteItem)
    {
        return $quoteItem->getNominalTotalDetails();
    }

    /**
     * Getter for details row label
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getItemDetailsRowLabel(\Magento\Framework\Object $row)
    {
        return $row->getLabel();
    }

    /**
     * Getter for details row amount
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getItemDetailsRowAmount(\Magento\Framework\Object $row)
    {
        return $row->getAmount();
    }

    /**
     * Getter for details row compounded state
     *
     * @param \Magento\Framework\Object $row
     * @return bool
     */
    public function getItemDetailsRowIsCompounded(\Magento\Framework\Object $row)
    {
        return $row->getIsCompounded();
    }

    /**
     * Format an amount without container
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceCurrency->format($amount, false);
    }

    /**
     * Import total data into the block, if there are items
     *
     * @return string
     */
    protected function _toHtml()
    {
        $total = $this->getTotal();
        $items = $total->getItems();
        if ($items) {
            foreach ($total->getData() as $key => $value) {
                $this->setData("total_{$key}", $value);
            }
            return parent::_toHtml();
        }
        return '';
    }
}
