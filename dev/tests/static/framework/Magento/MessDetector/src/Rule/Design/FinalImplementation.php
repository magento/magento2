<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessDetector\Rule\Design;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\ClassAware;
use PHPMD\Rule\MethodAware;

/**
 * Magento is a highly extensible and customizable platform.
 * Usage of final classes and methods is prohibited.
 */
class FinalImplementation extends AbstractRule implements ClassAware, MethodAware
{

    /**
     * @inheritdoc
     */
    public function apply(AbstractNode $node)
    {
        if ($node->isFinal()) {
            $this->addViolation($node, [$node->getType(), $node->getFullQualifiedName()]);
        }
    }
}