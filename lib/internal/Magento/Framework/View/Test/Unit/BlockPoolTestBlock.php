<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\View\Element\BlockInterface;

/**
 * Class BlockPoolTestBlock mock
 */
class BlockPoolTestBlock implements BlockInterface
{
    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '';
    }
}
