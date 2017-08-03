<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

use Magento\Search\Model\Autocomplete\ItemInterface;

/**
 * @api
 * @since 2.0.0
 */
interface AutocompleteInterface
{
    /**
     * @return ItemInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
