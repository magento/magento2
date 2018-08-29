<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Comparator allows to compare only sensitive params of 2 nodes
 * that can come from different places.
 */
class Comparator
{
    /**
     * Compare elements.
     *
     * @param ElementInterface | ElementDiffAwareInterface $first
     * @param ElementInterface | ElementDiffAwareInterface $second
     * @return bool
     */
    public function compare(ElementInterface $first, ElementInterface $second)
    {
        return get_class($first) === get_class($second) &&
            $first->getDiffSensitiveParams() === $second->getDiffSensitiveParams();
    }
}
