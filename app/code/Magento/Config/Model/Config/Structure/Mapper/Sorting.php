<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Sorting Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

class Sorting extends \Magento\Config\Model\Config\Structure\AbstractMapper
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        foreach ($data['config']['system'] as &$element) {
            $element = $this->_processConfig($element);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _processConfig($data)
    {
        foreach ($data as &$item) {
            if ($this->_hasValue('children', $item)) {
                $item['children'] = $this->_processConfig($item['children']);
            }
        }
        uasort($data, [$this, '_cmp']);
        return $data;
    }

    /**
     * Compare elements
     *
     * @param array $elementA
     * @param array $elementB
     * @return int
     */
    protected function _cmp($elementA, $elementB)
    {
        $sortIndexA = 0;
        if ($this->_hasValue('sortOrder', $elementA)) {
            $sortIndexA = floatval($elementA['sortOrder']);
        }
        $sortIndexB = 0;
        if ($this->_hasValue('sortOrder', $elementB)) {
            $sortIndexB = floatval($elementB['sortOrder']);
        }

        if ($sortIndexA == $sortIndexB) {
            return 0;
        }

        return $sortIndexA < $sortIndexB ? -1 : 1;
    }
}
