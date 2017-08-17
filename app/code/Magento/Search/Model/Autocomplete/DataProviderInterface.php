<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

/**
 * @api
 */
interface DataProviderInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
