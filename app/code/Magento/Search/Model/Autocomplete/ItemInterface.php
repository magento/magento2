<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

interface ItemInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return array
     */
    public function toArray();
}
