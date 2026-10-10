<?php
namespace Magento\WeeeConfigurableProducts\Model\Total\Quote;
 
/**
 * Class TotalQuoteWeee
 */
class Weee extends \Magento\Weee\Model\Total\Quote\Weee
{
    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     */
    protected function process(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Quote\Model\Quote\Address\Total $total,
        $item
    ) {
        // Add a reference to the quote item on the product model:
        $item->getProduct()->setQuoteItem($item);

        parent::process($address, $total, $item);
    }
}