<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Comparator allows to compare only sensitive params of 2 nodes
 * that can come from different places
 */
class Comparator
{
    /**
     * @param ElementInterface | ElementDiffAwareInterface $first
     * @param ElementInterface | ElementDiffAwareInterface  $second
     * @return bool
     */
    public function compare(ElementInterface $first, ElementInterface $second)
    {
        return get_class($first) === get_class($second) &&
            $first->getDiffSensitiveParams() === $second->getDiffSensitiveParams();
    }
}
