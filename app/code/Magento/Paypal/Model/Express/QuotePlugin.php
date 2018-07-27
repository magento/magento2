<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Express;

use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ProductOptionFactory;

/**
 * Plugin for Magento\Quote\Model\QuoteRepository\SaveHandler.
 *
 * Replaces cart item product options for disabled quote
 * which prevents it to be processed after placement of order
 * via PayPal Express payment solution.
 */
class QuotePlugin
{
    /**
     * @var ProductOptionFactory
     */
    private $productOptionFactory;

    /**
     * @param ProductOptionFactory $productOptionFactory
     */
    public function __construct(ProductOptionFactory $productOptionFactory)
    {
        $this->productOptionFactory = $productOptionFactory;
    }
    
    /**
     * Replace cart item product options for disabled quote.
     *
     * @param SaveHandler $subject
     * @param CartInterface $quote
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(SaveHandler $subject, CartInterface $quote)
    {
        if (!$quote->getIsActive()) {
            $items = $quote->getItems();

            if ($items) {
                foreach ($items as $item) {
                    /** @var \Magento\Quote\Model\Quote\Item $item */
                    if (!$item->isDeleted()) {
                        $item->setProductOption($this->productOptionFactory->create());
                    }
                }
            }
        }

        return [$quote];
    }
}
