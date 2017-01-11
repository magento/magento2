<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractQuoteItem;
use Magento\Catalog\Model\Product\Option as ProductOption;

/**
 * Plugin for Magento\Quote\Model\Quote\Item\ToOrderItem
 */
class QuoteItemProductOption
{
    /**
     * Perform preparations for custom options
     *
     * @param QuoteToOrderItem $subject
     * @param AbstractQuoteItem $quoteItem
     * @param array $data
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(
        QuoteToOrderItem $subject,
        AbstractQuoteItem $quoteItem,
        $data = []
    ) {
        if (!is_array($quoteItem->getOptions())) {
            return;
        }

        foreach ($quoteItem->getOptions() as $itemOption) {
            $code = explode('_', $itemOption->getCode());

            if (!isset($code[1]) || !is_numeric($code[1])) {
                continue;
            }

            $option = $quoteItem->getProduct()->getOptionById($code[1]);

            if (!$option || $option->getType() != ProductOption::OPTION_TYPE_FILE) {
                continue;
            }

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
