<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

/**
 * @api
 * @since 2.0.0
 */
interface SearchCollectionInterface extends \Traversable, \Countable
{
    /**
     * Set term filter
     *
     * @param string $term
     * @return self
     * @since 2.0.0
     */
    public function addSearchFilter($term);
}
