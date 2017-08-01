<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Dependencies Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

/**
 * @api
 * @since 2.0.0
 */
class Dependencies extends \Magento\Config\Model\Config\Structure\AbstractMapper
{
    /**
     * Class that can convert relative paths from "depends" node to absolute
     *
     * @var \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter
     * @since 2.0.0
     */
    protected $_pathConverter;

    /**
     * @param \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
    ) {
        $this->_pathConverter = $pathConverted;
    }

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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getDependPath($field, $config)
    {
        $dependPath = $field['id'];
        $elementPath = $config['path'] . '/' . $config['id'];

        return explode('/', $this->_pathConverter->convert($elementPath, $dependPath));
    }
}
