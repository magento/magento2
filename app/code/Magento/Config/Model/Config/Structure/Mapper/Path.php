<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Path Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

/**
 * @api
 * @since 2.0.0
 */
class Path extends \Magento\Config\Model\Config\Structure\AbstractMapper
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
