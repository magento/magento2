<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Dependencies Mapper
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Dependencies extends \Magento\Backend\Model\Config\Structure\AbstractMapper
{
    /**
     * Class that can convert relative paths from "depends" node to absolute
     *
     * @var \Magento\Backend\Model\Config\Structure\Mapper\Helper\RelativePathConverter
     */
    protected $_pathConverter;

    /**
     * @param \Magento\Backend\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
     */
    public function __construct(
        \Magento\Backend\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
    ) {
        $this->_pathConverter = $pathConverted;
    }

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
                $sectionConfig = $this->_processConfig($sectionConfig);
            }
        }
        return $data;
    }

    /**
     * Process configuration
     *
     * @param array $config
     * @return array
     */
    protected function _processConfig($config)
    {
        $config = $this->_processDepends($config);

        if ($this->_hasValue('children', $config)) {
            foreach ($config['children'] as &$subConfig) {
                $subConfig = $this->_processConfig($subConfig);
            }
        }
        return $config;
    }

    /**
     * Process dependencies configuration
     *
     * @param array $config
     * @return array
     */
    protected function _processDepends($config)
    {
        if ($this->_hasValue('depends/fields', $config)) {
            foreach ($config['depends']['fields'] as &$field) {
                $dependPath = $this->_getDependPath($field, $config);
                $field['dependPath'] = $dependPath;
                $field['id'] = implode('/', $dependPath);
            }
        }
        return $config;
    }

    /**
     * Get depend path
     *
     * @param array $field
     * @param array $config
     * @return string[]
     * @throws \InvalidArgumentException
     */
    protected function _getDependPath($field, $config)
    {
        $dependPath = $field['id'];
        $elementPath = $config['path'] . '/' . $config['id'];

        return explode('/', $this->_pathConverter->convert($elementPath, $dependPath));
    }
}
