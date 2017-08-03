<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

/**
 * @api
 * @since 2.0.0
 */
interface DataProviderInterface
{
    /**
     * @return ItemInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
