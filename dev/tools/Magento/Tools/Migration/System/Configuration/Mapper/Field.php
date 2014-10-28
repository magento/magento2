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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Migration\System\Configuration\Mapper;

class Field extends \Magento\Tools\Migration\System\Configuration\Mapper\AbstractMapper
{
    /**
     * List of allowed node names
     *
     * @var string[]
     */
    protected $_allowedFieldNames = array(
        'label',
        'comment',
        'tooltip',
        'frontend_class',
        'validate',
        'can_be_empty',
        'if_module_enabled',
        'frontend_model',
        'backend_model',
        'source_model',
        'config_path',
        'base_url',
        'upload_dir',
        'button_url',
        'button_label',
        'depends',
        'more_url',
        'demo_url',
        'hide_in_single_store_mode'
    );

    /**
     * Transform field config
     *
     * @param array $config
     * @return array
     */
    public function transform(array $config)
    {
        $output = array();
        foreach ($config as $fieldName => $fieldConfig) {
            $output[] = $this->_transformElement($fieldName, $fieldConfig, 'field', $this->_allowedFieldNames);
        }
        return $output;
    }

    /**
     * Transform sub configuration
     *
     * @param array $config
     * @param array $parentNode
     * @param array $element
     * @return array
     */
    public function _transformSubConfig(array $config, $parentNode, $element)
    {
        switch ($parentNode['name']) {
            case 'depends':
                $parentNode['subConfig'] = $this->_transformElementDepends($config);
                break;

            case 'attribute':
                $parentNode['subConfig'] = $this->_transformElementAttribute($config);
                break;
        }

        $element['parameters'][] = $parentNode;

        return $element;
    }

    /**
     * Transform depends configuration
     *
     * @param array $config
     * @return array
     */
    protected function _transformElementDepends(array $config)
    {
        $result = array();
        foreach ($config as $nodeName => $nodeValue) {
            $element = array();
            $element['nodeName'] = 'field';
            $element['@attributes']['id'] = $nodeName;
            $attributes = $this->_getValue($nodeValue, '@attributes', array());
            $element = $this->_transformAttributes($attributes, $element);

            if (false === empty($attributes)) {
                unset($nodeValue['@attributes']);
            }

            $element['#text'] = $nodeValue['#text'];
            $result[] = $element;
        }

        return $result;
    }

    /**
     * Transform element configuration
     *
     * @param array $config
     * @return array
     */
    protected function _transformElementAttribute(array $config)
    {
        $result = array();
        foreach ($config as $nodeName => $nodeValue) {
            $element = array();
            $element['nodeName'] = $nodeName;
            $attributes = $this->_getValue($nodeValue, '@attributes', array());
            $element = $this->_transformAttributes($attributes, $element);

            if (false === empty($attributes)) {
                unset($nodeValue['@attributes']);
            }
            if ($this->_isSubConfigValue($nodeValue)) {
                $element['subConfig'] = $this->_transformElementAttribute($nodeValue);
            } else {
                if ($this->_getValue($nodeValue, '#text', false)) {
                    $element['#text'] = $this->_getValue($nodeValue, '#text');
                }
                if ($this->_getValue($nodeValue, '#cdata-section', false)) {
                    $element['#cdata-section'] = $this->_getValue($nodeValue, '#cdata-section');
                }
            }

            $result[] = $element;
        }

        return $result;
    }
}
