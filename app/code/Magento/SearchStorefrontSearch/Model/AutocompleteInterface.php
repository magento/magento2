<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SearchStorefrontSearch\Model;

use Magento\SearchStorefrontSearch\Model\Autocomplete\ItemInterface;

/**
 * @api
 * @since 100.0.2
 */
interface AutocompleteInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
