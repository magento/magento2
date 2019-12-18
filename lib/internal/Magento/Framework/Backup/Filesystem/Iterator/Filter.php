<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Iterator;

use Iterator;

/**
 * Filter \Iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Filter extends \FilterIterator
{
    /**
     * Array that is used for filtering
     *
     * @var array
     */
    protected $filters;

    /**
     * Constructor
     *
     * @param Iterator $iterator
     * @param array $filters list of files or folders to skip
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        parent::__construct($iterator);
        $this->filters = $filters;
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool
     */
    public function accept()
    {
        $current = str_replace('\\', '/', $this->current()->__toString());
        $currentFilename = str_replace('\\', '/', $this->current()->getFilename());

        if ($currentFilename == '.' || $currentFilename == '..') {
            return false;
        }

        foreach ($this->filters as $filter) {
            $filter = str_replace('\\', '/', $filter);
            if ($current === $filter || false !== strpos($current, $filter . '/')) {
                return false;
            }
        }

        return true;
    }
}
