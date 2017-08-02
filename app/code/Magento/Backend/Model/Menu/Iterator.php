<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu;

/**
 * Menu iterator
 * @api
 * @since 2.0.0
 */
class Iterator extends \ArrayIterator
{
    /**
     * Rewind to first element
     *
     * @return void
     * @since 2.0.0
     */
    public function rewind()
    {
        $this->ksort();
        parent::rewind();
    }
}
