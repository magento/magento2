<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CodeMessDetector\Rule\Design;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\ClassNode;
use PHPMD\Node\MethodNode;
use PDepend\Source\AST\ASTMethod;
use PHPMD\Rule\MethodAware;

/**
 * Detect PHP serialization aware methods.
 */
class SerializationAware extends AbstractRule implements MethodAware
{
    /**
     * @inheritDoc
     *
     * @param ASTMethod|MethodNode $method
     */
    public function apply(AbstractNode $method)
    {
        if ($method->getName() === '__wakeup' || $method->getName() === '__sleep') {
            $this->addViolation($method, [$method->getName(), $method->getParent()->getFullQualifiedName()]);
        }
    }
}
