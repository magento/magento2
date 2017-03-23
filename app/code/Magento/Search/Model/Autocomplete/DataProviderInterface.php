<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

interface DataProviderInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
