<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CodeMessDetector\Rule\UnusedCode;

use PDepend\Source\AST\ASTParameter;
use PHPMD\AbstractNode;
use PHPMD\Node\ClassNode;
use PHPMD\Node\MethodNode;
use PHPMD\Rule\UnusedFormalParameter as PhpmdUnusedFormalParameter;

class UnusedFormalParameter extends PhpmdUnusedFormalParameter
{
    /**
     * This method collects all local variables in the body of the currently
     * analyzed method or function and removes those parameters that are
     * referenced by one of the collected variables.
     *
     * @param AbstractNode $node
     * @return void
     */
    protected function removeUsedParameters(AbstractNode $node)
    {
        parent::removeUsedParameters($node);
        $this->removeVariablesUsedInPlugins($node);
    }

    /**
     * Remove required method variables used in plugins from given node
     *
     * @param AbstractNode $node
     */
    private function removeVariablesUsedInPlugins(AbstractNode $node)
    {
        if ($node instanceof MethodNode) {
            /** @var ClassNode $classNode */
            $classNode = $node->getParentType();
            if ($this->isPluginClass($classNode->getNamespaceName())) {
                /**
                 * Around and After plugins has 2 required params $subject and $proceed or $result
                 * that should be ignored
                 */
                foreach (['around', 'after'] as $pluginMethodPrefix) {
                    if ($this->isFunctionNameStartingWith($node, $pluginMethodPrefix)) {
                        $this->removeVariablesByCount($node, 2);

                        break;
                    }
                }

                /**
                 * Before plugins has 1 required params $subject
                 * that should be ignored
                 */
                if ($this->isFunctionNameStartingWith($node, 'before')) {
                    $this->removeVariablesByCount($node, 1);
                }
            }
        }
    }

    /**
     * Check if the first part of function fully qualified name is equal to $name
     *
     * @param MethodNode $node
     * @param string $name
     * @return boolean
     */
    private function isFunctionNameStartingWith(MethodNode $node, string $name): bool
    {
        return (0 === strpos($node->getImage(), $name));
    }

    /**
     * Get first $numberOfParams parameters of method
     *
     * @param MethodNode $node
     * @param int $numberOfParams
     * @return array
     */
    private function getMethodParametersByLength(MethodNode $node, int $numberOfParams): array
    {
        return array_slice($node->getNode()->getParameters(), 0, $numberOfParams);
    }

    /**
     * Remove first $countOfRemovingVariables from given node
     *
     * @param MethodNode $node
     * @param int $countOfRemovingVariables
     */
    private function removeVariablesByCount(MethodNode $node, int $countOfRemovingVariables)
    {
        $methodParameters = $this->getMethodParametersByLength($node, $countOfRemovingVariables);
        /** @var ASTParameter $methodParameter */
        foreach ($methodParameters as $methodParameter) {
            unset($this->nodes[$methodParameter->getName()]);
        }
    }

    /**
     * Check if namespace contain "Plugin"
     * @param $class
     * @return bool
     */
    private function isPluginClass($class): bool
    {
        return (stripos($class, 'Plugin') !== false);
    }
}
