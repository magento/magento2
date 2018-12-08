<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;

class MergeQuoteItems
{
    /**
     * Resolves gift message to be
     * applied to merged quote items.
     *
     * @param Processor $subject
     * @param Item $result
     * @param Item $source
     * @return Item
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
<<<<<<< HEAD
    public function afterMerge(Processor $subject, Item $result, Item $source)
=======
    public function afterMerge(Processor $subject, Item $result, Item $source): Item
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $giftMessageId = $source->getGiftMessageId();

        if ($giftMessageId) {
            $result->setGiftMessageId($giftMessageId);
        }

        return $result;
    }
}
