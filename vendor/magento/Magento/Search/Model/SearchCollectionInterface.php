<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
