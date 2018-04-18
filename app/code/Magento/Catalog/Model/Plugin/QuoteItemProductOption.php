<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

class QuoteItemProductOption
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);

        if (is_array($item->getOptions())) {
            foreach ($item->getOptions() as $itemOption) {
                $code = explode('_', $itemOption->getCode());
                if (isset($code[1]) && is_numeric($code[1])) {
                    $option = $item->getProduct()->getOptionById($code[1]);
                    if ($option && $option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FILE) {
                        try {
                            $option->groupFactory(
                                $option->getType()
                            )->setQuoteItemOption(
                                $itemOption
                            )->copyQuoteToOrder();
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
        return $orderItem;
    }
}
