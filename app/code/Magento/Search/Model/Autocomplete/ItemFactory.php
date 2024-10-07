<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

use Magento\Framework\ObjectManagerInterface;
use Magento\Search\Model\Autocomplete\Item as AutocompleteItem;

class ItemFactory
{
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * @param array $data
     * @return Item
     */
    public function create(array $data)
    {
        return $this->objectManager->create(AutocompleteItem::class, ['data' => $data]);
    }
}
