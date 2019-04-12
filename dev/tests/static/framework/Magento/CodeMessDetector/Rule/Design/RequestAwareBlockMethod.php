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
 * Detect direct request usages.
 */
class RequestAwareBlockMethod extends AbstractRule implements MethodAware
{
    /**
     * @inheritDoc
     *
     * @param ASTMethod|MethodNode $method
     */
    public function apply(AbstractNode $method)
    {
        $definedIn = $method->getParentType();
        try {
            $isBlock = ($definedIn instanceof ClassNode)
                && is_subclass_of(
                    $definedIn->getFullQualifiedName(),
                    \Magento\Framework\View\Element\AbstractBlock::class
                );
        } catch (\Throwable $exception) {
            //Failed to load classes.
            return;
        }

        if ($isBlock) {
            $nodes = $method->findChildrenOfType('PropertyPostfix') + $method->findChildrenOfType('MethodPostfix');
            foreach ($nodes as $node) {
                $name = mb_strtolower($node->getFirstChildOfType('Identifier')->getImage());
                if ($name === '_request' || $name === 'getrequest') {
                    $this->addViolation($method, [$method->getFullQualifiedName()]);
                    break;
                }
            }
        }
    }
}
