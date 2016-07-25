<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

class QuoteItemProductOption
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
    ) {
        if (is_array($quoteItem->getOptions())) {
            foreach ($quoteItem->getOptions() as $itemOption) {
                $code = explode('_', $itemOption->getCode());

                if (isset($code[1]) && is_numeric($code[1])) {
                    $option = $quoteItem->getProduct()->getOptionById($code[1]);

                    if ($option && $option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FILE) {
                        try {
                            $option->groupFactory($option->getType())
                                ->setQuoteItemOption($itemOption)
                                ->copyQuoteToOrder();
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
    }
}
