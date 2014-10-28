<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System Configuration Sorting Mapper
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Sorting extends \Magento\Backend\Model\Config\Structure\AbstractMapper
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
        uasort($data, array($this, '_cmp'));
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
            $sortIndexA = intval($elementA['sortOrder']);
        }
        $sortIndexB = 0;
        if ($this->_hasValue('sortOrder', $elementB)) {
            $sortIndexB = intval($elementB['sortOrder']);
        }

        if ($sortIndexA == $sortIndexB) {
            return 0;
        }

        return $sortIndexA < $sortIndexB ? -1 : 1;
    }
}
