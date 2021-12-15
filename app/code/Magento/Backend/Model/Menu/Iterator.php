<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu;

/**
 * Menu iterator
 * @api
 * @since 100.0.2
 */
class Iterator extends \ArrayIterator
{
    /**
     * Rewind to first element
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->ksort();
        parent::rewind();
    }
}
