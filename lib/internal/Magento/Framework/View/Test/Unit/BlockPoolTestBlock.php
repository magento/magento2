<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
