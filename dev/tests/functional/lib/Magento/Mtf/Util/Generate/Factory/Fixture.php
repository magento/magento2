<?php
/**
 * @api
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Factory;

/**
 * Class Fixture
 *
 * Fixture Factory generator
 *
 */
class Fixture extends AbstractFactory
{
    protected $type = 'Fixture';

    /**
     * Collect Items
     */
    protected function generateContent()
    {
        $items = $this->collectItems('Fixture');
        foreach ($items as $item) {
            $this->addFixtureToFactory($item);
        }
    }

    /**
     * Add Fixture content
     *
     * @param array $item
     */
    protected function addFixtureToFactory($item)
    {
        list($module, $name) = explode('Test\\Fixture', $item['class']);
        $methodNameSuffix = $module . $name;
        $methodNameSuffix = $this->_toCamelCase($methodNameSuffix);
        $realClass = $this->_resolveClass($item);
        $fallbackComment = $this->_buildFallbackComment($item);

        $this->factoryContent .= "\n";
        $this->factoryContent .= "    /**\n";
        $this->factoryContent .= "     * @return {$item['class']}\n";
        $this->factoryContent .= "     */\n";
        $this->factoryContent .= "    public function get{$methodNameSuffix}(array \$placeholders = [])\n";
        $this->factoryContent .= "    {";

        if (!empty($fallbackComment)) {
            $this->factoryContent .= $fallbackComment . "\n";
        } else {
            $this->factoryContent .= "\n";
        }

        $this->factoryContent .= "        return \$this->objectManager->create(
            {$realClass}::class, "
            . "array('placeholders' => \$placeholders));\n";
        $this->factoryContent .= "    }\n";

        $this->cnt++;
    }
}
