<?php
/**
 * @api
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Factory;

/**
 * Page Factory generator.
 *
 */
class Page extends AbstractFactory
{
    protected $type = 'Page';

    /**
     * Collect Items.
     */
    protected function generateContent()
    {
        $items = $this->collectItems('Page');

        foreach ($items as $item) {
            $this->_addPageToFactory($item);
        }
    }

    /**
     * Convert class name to camel-case.
     *
     * @param string $class
     * @return string
     */
    private function toCamelCase($class)
    {
        $classNameExcessSymbols = ['_', '\\', '/', 'https:', 'http:', 'com', 'www', '.', '-'];
        $class = str_replace($classNameExcessSymbols, ' ', $class);
        return str_replace(' ', '', ucwords($class));
    }

    /**
     * Add Page to content.
     *
     * @param array $item
     */
    protected function _addPageToFactory($item)
    {
        $realClass = $this->_resolveClass($item);
        $reflectionClass = new \ReflectionClass($realClass);
        $mca = $reflectionClass->getConstant('MCA');
        $methodNameSuffix = $this->toCamelCase($mca);

        $fallbackComment = $this->_buildFallbackComment($item);

        $this->factoryContent .= "\n    /**\n";
        $this->factoryContent .= "     * @return {$item['class']}\n";
        $this->factoryContent .= "     */\n";
        $this->factoryContent .= "    public function get{$methodNameSuffix}()\n";
        $this->factoryContent .= "    {";

        if (!empty($fallbackComment)) {
            $this->factoryContent .= $fallbackComment . "\n";
        } else {
            $this->factoryContent .= "\n";
        }

        $this->factoryContent .= "        return \$this->objectManager->create('{$realClass}');";
        $this->factoryContent .= "\n    }\n";

        $this->cnt++;
    }
}
