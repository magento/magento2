<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Rss\NotifyStock;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

/**
 * Provide description for rss item.
 */
class DescriptionProvider
{
    /**
     * @param AbstractModel $item
     *
     * @return Phrase
     */
    public function execute(AbstractModel $item): Phrase
    {
        $qty = 1 * $item->getData('qty');
        $description = __('%1 has reached a quantity of %2.', $item->getData('name'), $qty);

        return $description;
    }
}
