<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

use Magento\Search\Model\Autocomplete\ItemInterface;

interface AutocompleteInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
