<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

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
