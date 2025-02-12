<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Filter \Iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup\Filesystem\Iterator;

use Iterator;

class Filter extends \FilterIterator
{
    /**
     * Array that is used for filtering
     *
     * @var array
     */
    protected $_filters;

    /**
     * Constructor
     *
     * @param Iterator $iterator
     * @param array $filters list of files to skip
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        parent::__construct($iterator);
        $this->_filters = $filters;
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function accept()
    {
        $current = str_replace('\\', '/', $this->current()->__toString() ?? '');
        $currentFilename = str_replace('\\', '/', $this->current()->getFilename() ?? '');

        if ($currentFilename == '.' || $currentFilename == '..') {
            return false;
        }

        foreach ($this->_filters as $filter) {
            $filter = $filter !== null ? str_replace('\\', '/', $filter) : '';
            if (false !== strpos($current, $filter)) {
                return false;
            }
        }

        return true;
    }
}
