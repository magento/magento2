<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model\AsyncResponse;

use Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface;

class ItemsList implements ItemsListInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @param array $items [optional]
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
