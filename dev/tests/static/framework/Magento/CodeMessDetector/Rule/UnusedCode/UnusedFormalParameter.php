<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CodeMessDetector\Rule\UnusedCode;

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
        if (!$node instanceof MethodNode) {
            return;
        }

        /** @var ClassNode $classNode */
        $classNode = $node->getParentType();
        if (!$this->isPluginClass($classNode->getNamespaceName())) {
            return;
        }

        /**
         * Around and After plugins has 2 required params $subject and $proceed or $result
         * that should be ignored
         */
        foreach (['around', 'after'] as $pluginMethodPrefix) {
            if ($this->isFunctionNameStartingWith($node, $pluginMethodPrefix)) {
                $this->removeVariablesByCount(2);

                break;
            }
        }

        /**
         * Before plugins has 1 required params $subject
         * that should be ignored
         */
        if ($this->isFunctionNameStartingWith($node, 'before')) {
            $this->removeVariablesByCount(1);
        }
    }

    /**
     * Check if the first part of function fully qualified name is equal to $name
     *
     * Methods getImage and getName are equal. getImage used prior to usage in phpmd source
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
     * Remove first $countOfRemovingVariables from given node
     *
     * @param int $countOfRemovingVariables
     */
    private function removeVariablesByCount(int $countOfRemovingVariables)
    {
        array_splice($this->nodes, 0, $countOfRemovingVariables);
    }

    /**
     * Check if namespace contain "Plugin". Case-sensitive ignored
     *
     * @param string $class
     * @return bool
     */
    private function isPluginClass(string $class): bool
    {
        return (stripos($class, 'plugin') !== false);
    }
}
