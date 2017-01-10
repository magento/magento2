<?php
/**
 * @api
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Factory;

/**
 * Class Handler
 *
 * Handler Factory generator
 *
 */
class Handler extends AbstractFactory
{
    protected $type = 'Handler';

    /**
     * Collect Items
     */
    protected function generateContent()
    {
        $items = $this->collectItems('Handler');

        $fallback = [
            0 => 'Curl',
            1 => 'Ui',
        ];

        $byTypes = [];
        foreach ($items as $item) {
            preg_match('/(\w*)\\\(\w*)\\\Test\\\Handler\\\(\w*)\\\(\w*)/', $item['class'], $matches);
            if (5 === count($matches)) {
                $methodNameSuffix = strtolower($matches[1]) . $matches[2] . $matches[4];
                foreach ($fallback as $pos => $type) {
                    if ($matches[3] === $type) {
                        if (!isset($byTypes[$methodNameSuffix])) {
                            $item['_fallback_position_'] = $pos;
                            $byTypes[$methodNameSuffix] = $item;
                        } else {
                            $_item = $byTypes[$methodNameSuffix];
                            $_pos = $_item['_fallback_position_'];
                            if ($_pos > $pos) {
                                $item['_fallback_position_'] = $pos;
                                $byTypes[$methodNameSuffix] = $item;
                            }
                        }
                        break;
                    }
                }
            }
        }

        foreach ($byTypes as $methodNameSuffix => $item) {
            $this->_addHandlerToFactory($methodNameSuffix, $item);
        }
    }

    /**
     * Add Handler content
     *
     * @param string $methodNameSuffix
     * @param array $item
     */
    protected function _addHandlerToFactory($methodNameSuffix, $item)
    {
        $fallbackComment = $this->_buildFallbackComment($item);
        $realClass = $this->_resolveClass($item);

        $this->factoryContent .= "\n    /**\n";
        $this->factoryContent .= "     * @return \\{$item['class']}\n";
        $this->factoryContent .= "     */\n";
        $this->factoryContent .= "    public function {$methodNameSuffix}(FixtureInterface \$fixture = null)\n";
        $this->factoryContent .= "    {";

        if (!empty($fallbackComment)) {
            $this->factoryContent .= $fallbackComment . "\n";
        } else {
            $this->factoryContent .= "\n";
        }

        $this->factoryContent .= "        \$handler = \$this->objectManager->get({$realClass}::class);\n";
        $this->factoryContent .= "        return \$handler->persist(\$fixture);";
        $this->factoryContent .= "\n    }\n";

        $this->cnt++;
    }
}
