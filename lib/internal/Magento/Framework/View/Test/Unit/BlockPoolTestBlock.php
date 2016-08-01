<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

/**
 * Class BlockPoolTestBlock mock
 */
class BlockPoolTestBlock implements \Magento\Framework\View\Element\BlockInterface
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
