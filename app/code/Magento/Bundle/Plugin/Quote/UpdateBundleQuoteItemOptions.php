<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Quote;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteManagement;

/**
 * Update bundle selection custom options
 */
class UpdateBundleQuoteItemOptions
{
    /**
     * @var Option
     */
    private $option;

    /**
     * @param Option $option
     */
    public function __construct(
        Option $option
    ) {
        $this->option = $option;
    }

    /**
     * Update bundle selection custom options before order is placed
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
        array $orderData = []
    ): void {
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getProductType() === Type::TYPE_CODE) {
                $options = $this->option->getSelectionOptions($quoteItem->getProduct());
                foreach ($quoteItem->getChildren() as $childItem) {
                    /** @var Item $childItem */
                    $customOption = $childItem->getOptionByCode('selection_id');
                    $selectionId = $customOption ? $customOption->getValue() : null;
                    if ($selectionId && isset($options[$selectionId])) {
                        $childItem->setOptions($options[$selectionId]);
                    }
                }
            }
        }
    }
}
