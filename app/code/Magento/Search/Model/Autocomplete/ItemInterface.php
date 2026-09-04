<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Model\Autocomplete;

/**
 * @api
 * @since 100.0.2
 */
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
