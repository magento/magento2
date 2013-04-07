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
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget model for different purposes
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Model_Widget extends Varien_Object
{
    /**
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_configReader;

    /**
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_configCacheType;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $configReader
     * @param Mage_Core_Model_Cache_Type_Config $configCacheType
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $configReader,
        Mage_Core_Model_Cache_Type_Config $configCacheType
    ) {
        $this->_configReader = $configReader;
        $this->_configCacheType = $configCacheType;
    }

    /**
     * Load Widgets XML config from widget.xml files and cache it
     *
     * @return Varien_Simplexml_Config
     */
    public function getXmlConfig()
    {
        $cacheId = 'widget_config';
        $cachedXml = $this->_configCacheType->load($cacheId);
        if ($cachedXml) {
            $xmlConfig = new Varien_Simplexml_Config($cachedXml);
        } else {
            $xmlConfig = new Varien_Simplexml_Config();
            $xmlConfig->loadString('<?xml version="1.0"?><widgets></widgets>');
            $this->_configReader->loadModulesConfiguration('widget.xml', $xmlConfig);
            $this->_configCacheType->save($xmlConfig->getXmlString(), $cacheId);
        }
        return $xmlConfig;
    }

    /**
     * Return widget XML config element based on its type
     *
     * @param string $type Widget type
     * @return null|Varien_Simplexml_Element
     */
    public function getXmlElementByType($type)
    {
        $elements = $this->getXmlConfig()->getXpath('*[@type="' . $type . '"]');
        if (is_array($elements) && isset($elements[0]) && $elements[0] instanceof Varien_Simplexml_Element) {
            return $elements[0];
        }
        return null;
    }

    /**
     * Wrapper for getXmlElementByType method
     *
     * @param string $type Widget type
     * @return null|Varien_Simplexml_Element
     */
    public function getConfigAsXml($type)
    {
        return $this->getXmlElementByType($type);
    }

    /**
     * Return widget XML configuration as Varien_Object and makes some data preparations
     *
     * @param string $type Widget type
     * @return Varien_Object
     */
    public function getConfigAsObject($type)
    {
        $xml = $this->getConfigAsXml($type);

        $object = new Varien_Object();
        if ($xml === null) {
            return $object;
        }

        // Save all nodes to object data
        $object->setType($type);
        $object->setData($xml->asCanonicalArray());

        // Set module for translations etc.
        $module = $object->getData('@/module');
        if ($module) {
            $object->setModule($module);
        }

        // Correct widget parameters and convert its data to objects
        $params = $object->getData('parameters');
        $newParams = array();
        if (is_array($params)) {
            $sortOrder = 0;
            foreach ($params as $key => $data) {
                if (is_array($data)) {
                    $data['key'] = $key;
                    $data['sort_order'] = isset($data['sort_order']) ? (int)$data['sort_order'] : $sortOrder;

                    // prepare values (for drop-dawns) specified directly in configuration
                    $values = array();
                    if (isset($data['values']) && is_array($data['values'])) {
                        foreach ($data['values'] as $value) {
                            if (isset($value['label']) && isset($value['value'])) {
                                $values[] = $value;
                            }
                        }
                    }
                    $data['values'] = $values;

                    // prepare helper block object
                    if (isset($data['helper_block'])) {
                        $helper = new Varien_Object();
                        if (isset($data['helper_block']['data']) && is_array($data['helper_block']['data'])) {
                            $helper->addData($data['helper_block']['data']);
                        }
                        if (isset($data['helper_block']['type'])) {
                            $helper->setType($data['helper_block']['type']);
                        }
                        $data['helper_block'] = $helper;
                    }

                    $newParams[$key] = new Varien_Object($data);
                    $sortOrder++;
                }
            }
        }
        uasort($newParams, array($this, '_sortParameters'));
        $object->setData('parameters', $newParams);

        return $object;
    }

    /**
     * Return filtered list of widgets as SimpleXml object
     *
     * @param array $filters Key-value array of filters for widget node properties
     * @return Varien_Simplexml_Element
     */
    public function getWidgetsXml($filters = array())
    {
        $widgets = $this->getXmlConfig()->getNode();
        $result = clone $widgets;

        // filter widgets by params
        if (is_array($filters) && count($filters) > 0) {
            foreach ($widgets as $code => $widget) {
                try {
                    $reflection = new ReflectionObject($widget);
                    foreach ($filters as $field => $value) {
                        if (!$reflection->hasProperty($field) || (string)$widget->{$field} != $value) {
                            throw new Exception();
                        }
                    }
                } catch (Exception $e) {
                    unset($result->{$code});
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * Return list of widgets as array
     *
     * @param array $filters Key-value array of filters for widget node properties
     * @return array
     */
    public function getWidgetsArray($filters = array())
    {
        if (!$this->_getData('widgets_array')) {
            $result = array();
            foreach ($this->getWidgetsXml($filters) as $widget) {
                $helper = $widget->getAttribute('module') ? $widget->getAttribute('module') : 'Mage_Widget_Helper_Data';
                $helper = Mage::helper($helper);
                $result[$widget->getName()] = array(
                    'name'          => $helper->__((string)$widget->name),
                    'code'          => $widget->getName(),
                    'type'          => $widget->getAttribute('type'),
                    'description'   => $helper->__((string)$widget->description)
                );
            }
            usort($result, array($this, "_sortWidgets"));
            $this->setData('widgets_array', $result);
        }
        return $this->_getData('widgets_array');
    }

    /**
     * Return widget presentation code in WYSIWYG editor
     *
     * @param string $type Widget Type
     * @param array $params Pre-configured Widget Params
     * @param bool $asIs Return result as widget directive(true) or as placeholder image(false)
     * @return string Widget directive ready to parse
     */
    public function getWidgetDeclaration($type, $params = array(), $asIs = true)
    {
        $directive = '{{widget type="' . $type . '"';

        foreach ($params as $name => $value) {
            // Retrieve default option value if pre-configured
            if (is_array($value)) {
                $value = implode(',', $value);
            } elseif (trim($value) == '') {
                $widget = $this->getConfigAsObject($type);
                $parameters = $widget->getParameters();
                if (isset($parameters[$name]) && is_object($parameters[$name])) {
                    $value = $parameters[$name]->getValue();
                }
            }
            if ($value) {
                $directive .= sprintf(' %s="%s"', $name, $value);
            }
        }
        $directive .= '}}';

        if ($asIs) {
            return $directive;
        }

        $html = sprintf('<img id="%s" src="%s" title="%s">',
            $this->_idEncode($directive),
            $this->getPlaceholderImageUrl($type),
            Mage::helper('Mage_Core_Helper_Data')->escapeUrl($directive)
        );
        return $html;
    }

    /**
     * Get image URL of WYSIWYG placeholder image
     *
     * @param string $type
     * @return string
     */
    public function getPlaceholderImageUrl($type)
    {
        $placeholder = false;
        $widgetXml = $this->getConfigAsXml($type);
        if (is_object($widgetXml)) {
            $placeholder = (string)$widgetXml->placeholder_image;
        }
        if (!$placeholder || !Mage::getDesign()->getViewFile($placeholder)) {
            $placeholder = 'Mage_Widget::placeholder.gif';
        }
        return Mage::getDesign()->getViewFileUrl($placeholder);
    }

    /**
     * Get a list of URLs of WYSIWYG placeholder images
     *
     * array(<type> => <url>)
     *
     * @return array
     */
    public function getPlaceholderImageUrls()
    {
        $result = array();
        /** @var Varien_Simplexml_Element $widget */
        foreach ($this->getXmlConfig()->getNode() as $widget) {
            $type = (string)$widget->getAttribute('type');
            $result[$type] = $this->getPlaceholderImageUrl($type);
        }
        return $result;
    }

    /**
     * Return list of required JS files to be included on the top of the page before insertion plugin loaded
     *
     * @return array
     */
    public function getWidgetsRequiredJsFiles()
    {
        $result = array();
        foreach ($this->getWidgetsXml() as $widget) {
            if ($widget->js) {
                foreach (explode(',', (string)$widget->js) as $js) {
                    $result[] = $js;
                }
            }
       }
       return $result;
    }

    /**
     * Encode string to valid HTML id element, based on base64 encoding
     *
     * @param string $string
     * @return string
     */
    protected function _idEncode($string)
    {
        return strtr(base64_encode($string), '+/=', ':_-');
    }

    /**
     * User-defined widgets sorting by Name
     *
     * @param array $a
     * @param array $b
     * @return boolean
     */
    protected function _sortWidgets($a, $b)
    {
        return strcmp($a["name"], $b["name"]);
    }

    /**
     * Widget parameters sort callback
     *
     * @param Varien_Object $a
     * @param Varien_Object $b
     * @return int
     */
    protected function _sortParameters($a, $b)
    {
        $aOrder = (int)$a->getData('sort_order');
        $bOrder = (int)$b->getData('sort_order');
        return $aOrder < $bOrder ? -1 : ($aOrder > $bOrder ? 1 : 0);
    }
}
