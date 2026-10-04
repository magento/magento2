<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Model;

/**
 * @api
 * @since 100.0.2
 */
interface SearchCollectionInterface extends \Traversable, \Countable
{
    /**
     * Set term filter
     *
     * @param string $term
     * @return self
     */
    public function addSearchFilter($term);
}
