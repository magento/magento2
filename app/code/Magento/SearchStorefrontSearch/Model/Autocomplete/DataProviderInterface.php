<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SearchStorefrontSearch\Model\Autocomplete;

/**
 * @api
 * @since 100.0.2
 */
interface DataProviderInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
