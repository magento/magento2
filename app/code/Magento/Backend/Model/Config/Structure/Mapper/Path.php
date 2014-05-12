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
 * System Configuration Path Mapper
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Path extends \Magento\Backend\Model\Config\Structure\AbstractMapper
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        if ($this->_hasValue('config/system/sections', $data)) {
            foreach ($data['config']['system']['sections'] as &$sectionConfig) {
                if ($this->_hasValue('children', $sectionConfig)) {
                    foreach ($sectionConfig['children'] as &$groupConfig) {
                        $groupConfig = $this->_processConfig($groupConfig, $sectionConfig);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Process configuration
     *
     * @param array $elementConfig
     * @param array $parentConfig
     * @return array
     */
    protected function _processConfig(array $elementConfig, array $parentConfig)
    {
        $parentPath = $this->_hasValue('path', $parentConfig) ? $parentConfig['path'] . '/' : '';
        $parentPath .= $parentConfig['id'];
        $elementConfig['path'] = $parentPath;

        if ($this->_hasValue('children', $elementConfig)) {
            foreach ($elementConfig['children'] as &$subConfig) {
                $subConfig = $this->_processConfig($subConfig, $elementConfig);
            }
        }

        return $elementConfig;
    }
}
