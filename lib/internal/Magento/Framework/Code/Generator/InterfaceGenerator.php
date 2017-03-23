<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

/**
 * Interface generator.
 */
class InterfaceGenerator extends \Magento\Framework\Code\Generator\ClassGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            $output = $this->getSourceContent();
            if (!empty($output)) {
                return $output;
            }
        }
        $output = '';
        if (!$this->getName()) {
            return $output;
        }

        $output .= $this->generateDirectives();
        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');
            $output .= $docBlock->generate();
        }
        $output .= 'interface ' . $this->getName();
        if (!empty($this->extendedClass)) {
            $output .= ' extends \\' . ltrim($this->extendedClass, '\\');
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED
            . $this->generateMethods() . self::LINE_FEED . '}' . self::LINE_FEED;

        return $output;
    }

    /**
     * Instantiate interface method generator object.
     *
     * @return \Magento\Framework\Code\Generator\InterfaceMethodGenerator
     */
    protected function createMethodGenerator()
    {
        return new \Magento\Framework\Code\Generator\InterfaceMethodGenerator();
    }

    /**
     * Generate methods.
     *
     * @return string
     */
    protected function generateMethods()
    {
        $output = '';
        $methods = $this->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $output .= $method->generate() . self::LINE_FEED;
            }
        }
        return $output;
    }

    /**
     * Generate directives.
     *
     * @return string
     */
    protected function generateDirectives()
    {
        $output = '';
        $namespace = $this->getNamespaceName();
        if (null !== $namespace) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        $uses = $this->getUses();
        if (!empty($uses)) {
            foreach ($uses as $use) {
                $output .= 'use ' . $use . ';' . self::LINE_FEED;
            }
            $output .= self::LINE_FEED;
        }
        return $output;
    }
}
