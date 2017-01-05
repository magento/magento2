<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu;

/**
 * Menu iterator
 */
class Iterator extends \ArrayIterator
{
    /**
     * Rewind to first element
     *
     * @return void
     */
    public function rewind()
    {
        $this->ksort();
        parent::rewind();
    }
}
