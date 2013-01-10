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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System Configuration Dependencies Mapper
 */
class Mage_Backend_Model_Config_Structure_Mapper_Dependencies extends Mage_Backend_Model_Config_Structure_MapperAbstract
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
     * @return array
     * @throws InvalidArgumentException
     */
    protected function _getDependPath($field, $config)
    {
        $dependPath = $field['id'];
        if (strpos($dependPath, '/') === false) {
            $dependPath = '*/*/' .  $dependPath;
        }
        $elementPath = $config['path'] . '/' . $config['id'];

        $dependPathParts = explode('/', $dependPath);
        $elementPathParts = explode('/', $elementPath);
        $output = array();
        foreach ($dependPathParts as $index => $path) {
            if ($path === '*') {
                if (false == array_key_exists($index, $elementPathParts)) {
                    throw new InvalidArgumentException('Invalid relative depends structure');
                }
                $path = $elementPathParts[$index];
            }
            $output[$index] = $path;
        }
        return $output;
    }
}
