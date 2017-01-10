<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor\Helper;

use Magento\Framework\Phrase;

/**
 * Class Sort
 */
class Sort implements SortInterface
{
    /**
     * Name of directive
     */
    const DIRECTIVE = 'after';

    /**
     * Key of name items
     */
    const NEXT_KEY = 'next';

    /**
     * @var array
     */
    private $result;

    /**
     * @var array
     */
    private $array;

    /**
     * @inheritdoc
     */
    public function sort(array $array)
    {
        $this->result = [];
        $this->array = $array;

        $nodes = [];
        $structure = [];
        foreach ($this->array as $name => $item) {
            $nodes[$name] = isset($nodes[$name]) ? $nodes[$name] : [self::NEXT_KEY => null];
            if (isset($item[self::DIRECTIVE])) {
                $nodes[$item[self::DIRECTIVE]][self::NEXT_KEY][$name] = &$nodes[$name];
                continue;
            }
            $structure[$name] = &$nodes[$name];
        }

        $this->fillResult($structure);

        return $this->result;
    }

    /**
     * @param array $structure
     * @return void
     */
    private function fillResult(array $structure)
    {
        foreach ($structure as $name => $item) {
            $this->result[$name] = $this->array[$name];
            if (!empty($item[self::NEXT_KEY])) {
                $this->fillResult($item[self::NEXT_KEY]);
            }
        }
    }
}
