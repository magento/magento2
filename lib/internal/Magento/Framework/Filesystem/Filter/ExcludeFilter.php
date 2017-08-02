<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Filter;

/**
 * Filters Iterator to exclude specified files
 * @since 2.1.0
 */
class ExcludeFilter extends \FilterIterator
{
    /**
     * Array that is used for filtering
     *
     * @var array
     * @since 2.1.0
     */
    protected $_filters;

    /**
     * Constructor
     *
     * @param \Iterator $iterator
     * @param array $filters list of files to skip
     * @since 2.1.0
     */
    public function __construct(\Iterator $iterator, array $filters)
    {
        parent::__construct($iterator);
        $this->_filters = $filters;
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool
     * @since 2.1.0
     */
    public function accept()
    {
        $current = str_replace('\\', '/', $this->current()->__toString());
        $currentFilename = str_replace('\\', '/', $this->current()->getFilename());

        if ($currentFilename == '.' || $currentFilename == '..') {
            return false;
        }

        foreach ($this->_filters as $filter) {
            $filter = str_replace('\\', '/', $filter);
            if (false !== strpos($current, $filter)) {
                return false;
            }
        }

        return true;
    }
}
