<?php
/**
 * @api
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Factory;

/**
 * Class Repository
 *
 * Repository Factory generator
 *
 */
class Repository extends AbstractFactory
{
    protected $type = 'Repository';

    /**
     * Collect Items
     */
    protected function generateContent()
    {
        $items = $this->collectItems('Repository');

        foreach ($items as $item) {
            $this->_addToFactory($item);
        }
    }

    /**
     * Add Fixture Repository
     *
     * @param array $item
     */
    protected function _addToFactory($item)
    {
        list($module, $name) = explode('Test\\Repository', $item['class']);
        $methodNameSuffix = $module . $name;
        $methodNameSuffix = $this->_toCamelCase($methodNameSuffix);

        $realClass = $this->_resolveClass($item);
        $fallbackComment = $this->_buildFallbackComment($item);

        $this->factoryContent .= "\n    /**\n";
        $this->factoryContent .= "     * @return {$item['class']}\n";
        $this->factoryContent .= "     */\n";
        $this->factoryContent .= "    public function get{$methodNameSuffix}(array \$defaultConfig = [], "
            . "array \$defaultData = [])\n";
        $this->factoryContent .= "    {";

        if (!empty($fallbackComment)) {
            $this->factoryContent .= $fallbackComment . "\n";
        } else {
            $this->factoryContent .= "\n";
        }

        $this->factoryContent .= "        return \$this->objectManager->create(
            {$realClass}::class, "
            . "array('defaultConfig' => \$defaultConfig, 'defaultData' => \$defaultData));\n";
        $this->factoryContent .= "    }\n";

        $this->cnt++;
    }
}
