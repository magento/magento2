<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CodeMessDetector\Rule\Design;

use PDepend\Source\AST\ASTClass;
use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\ClassNode;
use PHPMD\Rule\ClassAware;
use Magento\Framework\App\ActionInterface;

/**
 * Actions must process a defined list of HTTP methods.
 */
class AllPurposeAction extends AbstractRule implements ClassAware
{
    /**
     * @param ClassNode|ASTClass $node
     *
     * @return string[]
     */
    private function extractInterfaces(ClassNode $node): array
    {
        $interfaces = [];
        foreach ($node->getInterfaces() as $interface) {
            $interfaces[] = $interface->getNamespacedName();
        }
        if ($parent = $node->getParentClass()) {
            $interfaces = array_merge(
                $interfaces,
                $this->extractInterfaces($parent)
            );
        }

        return array_unique($interfaces);
    }

    /**
     * @inheritdoc
     *
     * @param ClassNode|ASTClass $node
     */
    public function apply(AbstractNode $node)
    {
        $impl = $this->extractInterfaces($node);

        if (in_array(ActionInterface::class, $impl, true)) {
            $methodsDefined = false;
            foreach ($impl as $i) {
                if (preg_match('/\\\Http[a-z]+MethodActionInterface$/i', $i)) {
                    $methodsDefined = true;
                    break;
                }
            }
            if (!$methodsDefined) {
                $this->addViolation($node, [$node->getFullQualifiedName()]);
            }
        }
    }
}
