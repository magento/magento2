<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\State;

/**
 * View state collection
 */
class Collection implements CollectionInterface
{
    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return [];
    }
}
