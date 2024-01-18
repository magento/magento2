<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template;

/**
 * Meter of template filtering depth.
 *
 * Records and provides template/directive filtering depth (filtering recursion).
 * Filtering depth 1 means that template or directive is root and has no parents.
 */
class FilteringDepthMeter
{
    /**
     * @var int
     */
    private $depth = 0;

    /**
     * Increases filtering depth.
     *
     * @return void
     */
    public function descend()
    {
        $this->depth++;
    }

    /**
     * Decreases filtering depth.
     *
     * @return void
     */
    public function ascend()
    {
        $this->depth--;
    }

    /**
     * Shows current filtering depth.
     *
     * @return int
     */
    public function showMark(): int
    {
        return $this->depth;
    }
}
