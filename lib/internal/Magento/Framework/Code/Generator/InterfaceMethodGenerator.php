<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

/**
 * Interface method generator.
 */
class InterfaceMethodGenerator extends \Zend\Code\Generator\MethodGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->validateMethodModifiers();
        $output = '';
        if (!$this->getName()) {
            return $output;
        }

        $indent = $this->getIndentation();

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation($indent);
            $output .= $docBlock->generate();
        }

        $output .= $indent;

        $output .= $this->getVisibility() . (($this->isStatic()) ? ' static' : '')
            . ' function ' . $this->getName() . '(';

        $parameters = $this->getParameters();
        if (!empty($parameters)) {
            $parameterOutput = [];
            foreach ($parameters as $parameter) {
                $parameterOutput[] = $parameter->generate();
            }
            $output .= implode(', ', $parameterOutput);
        }

        $output .= ');' . self::LINE_FEED;

        return $output;
    }

    /**
     * Ensure that used method modifiers are allowed for interface methods.
     *
     * @throws \LogicException
     * @return void
     */
    protected function validateMethodModifiers()
    {
        if ($this->getVisibility() != self::VISIBILITY_PUBLIC) {
            throw new \LogicException(
                "Interface method visibility can only be 'public'. Method name: '{$this->getName()}'"
            );
        }
        if ($this->isFinal()) {
            throw new \LogicException(
                "Interface method cannot be marked as 'final'. Method name: '{$this->getName()}'"
            );
        }
        if ($this->isAbstract()) {
            throw new \LogicException(
                "'abstract' modifier cannot be used for interface method. Method name: '{$this->getName()}'"
            );
        }
    }
}
