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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance Model
 *
 * @method Mage_Widget_Model_Resource_Widget_Instance _getResource()
 * @method Mage_Widget_Model_Resource_Widget_Instance getResource()
 * @method string getTitle()
 * @method Mage_Widget_Model_Widget_Instance setTitle(string $value)
 * @method Mage_Widget_Model_Widget_Instance setStoreIds(string $value)
 * @method Mage_Widget_Model_Widget_Instance setWidgetParameters(string $value)
 * @method int getSortOrder()
 * @method Mage_Widget_Model_Widget_Instance setSortOrder(int $value)
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Model_Widget_Instance extends Mage_Core_Model_Abstract
{
    const SPECIFIC_ENTITIES = 'specific';
    const ALL_ENTITIES      = 'all';

    const DEFAULT_LAYOUT_HANDLE            = 'default';
    const PRODUCT_LAYOUT_HANDLE            = 'catalog_product_view';
    const SINGLE_PRODUCT_LAYOUT_HANLDE     = 'catalog_product_view_id_{{ID}}';
    const PRODUCT_TYPE_LAYOUT_HANDLE       = 'catalog_product_view_type_{{TYPE}}';
    const ANCHOR_CATEGORY_LAYOUT_HANDLE    = 'catalog_category_view_type_layered';
    const NOTANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_type_default';
    const SINGLE_CATEGORY_LAYOUT_HANDLE    = 'catalog_category_view_{{ID}}';

    const XML_NODE_RELATED_CACHE = 'global/widget/related_cache_types';

    protected $_layoutHandles = array();

    protected $_specificEntitiesLayoutHandles = array();

    /**
     * @var Varien_Simplexml_Element
     */
    protected $_widgetConfigXml = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'widget_widget_instance';

    /**
     * Internal Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Widget_Model_Resource_Widget_Instance');
        $this->_layoutHandles = array(
            'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::PRODUCT_LAYOUT_HANDLE,
            'all_pages' => self::DEFAULT_LAYOUT_HANDLE
        );
        $this->_specificEntitiesLayoutHandles = array(
            'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANLDE,
        );
        foreach (Mage_Catalog_Model_Product_Type::getTypes() as $typeId => $type) {
            $layoutHandle = str_replace('{{TYPE}}', $typeId, self::PRODUCT_TYPE_LAYOUT_HANDLE);
            $this->_layoutHandles[$typeId . '_products'] = $layoutHandle;
            $this->_specificEntitiesLayoutHandles[$typeId . '_products'] = self::SINGLE_PRODUCT_LAYOUT_HANLDE;
        }
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Widget_Model_Widget_Instance
     */
    protected function _beforeSave()
    {
        $pageGroupIds = array();
        $tmpPageGroups = array();
        $pageGroups = $this->getData('page_groups');
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id']) {
                        $pageGroupIds[] = $pageGroupData['page_id'];
                    }
                    if ($pageGroup['page_group'] == 'pages') {
                        $layoutHandle = $pageGroupData['layout_handle'];
                    } else {
                        $layoutHandle = $this->_layoutHandles[$pageGroup['page_group']];
                    }
                    if (!isset($pageGroupData['template'])) {
                        $pageGroupData['template'] = '';
                    }
                    $tmpPageGroup = array(
                        'page_id' => $pageGroupData['page_id'],
                        'group' => $pageGroup['page_group'],
                        'layout_handle' => $layoutHandle,
                        'for' => $pageGroupData['for'],
                        'block_reference' => $pageGroupData['block'],
                        'entities' => '',
                        'layout_handle_updates' => array($layoutHandle),
                        'template' => $pageGroupData['template']?$pageGroupData['template']:''
                    );
                    if ($pageGroupData['for'] == self::SPECIFIC_ENTITIES) {
                        $layoutHandleUpdates = array();
                        foreach (explode(',', $pageGroupData['entities']) as $entity) {
                            $layoutHandleUpdates[] = str_replace('{{ID}}', $entity,
                                $this->_specificEntitiesLayoutHandles[$pageGroup['page_group']]);
                        }
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                        $tmpPageGroup['layout_handle_updates'] = $layoutHandleUpdates;
                    }
                    $tmpPageGroups[] = $tmpPageGroup;
                }
            }
        }
        if (is_array($this->getData('store_ids'))) {
            $this->setData('store_ids', implode(',', $this->getData('store_ids')));
        }
        if (is_array($this->getData('widget_parameters'))) {
            $this->setData('widget_parameters', serialize($this->getData('widget_parameters')));
        }
        $this->setData('page_groups', $tmpPageGroups);
        $this->setData('page_group_ids', $pageGroupIds);

        return parent::_beforeSave();
    }

    /**
     * Validate widget instance data
     *
     * @return string|boolean
     */
    public function validate()
    {
        if ($this->isCompleteToCreate()) {
            return true;
        }
        return Mage::helper('Mage_Widget_Helper_Data')->__('Widget instance is not full complete to create.');
    }

    /**
     * Check if widget instance has required data (other data depends on it)
     *
     * @return boolean
     */
    public function isCompleteToCreate()
    {
        return (bool)($this->getType() && $this->getPackageTheme());
    }

    /**
     * Setter
     * Prepare widget type
     *
     * @param string $type
     * @return Mage_Widget_Model_Widget_Instance
     */
    public function setType($type)
    {
        $this->setData('instance_type', $type);
        return $this;
    }

    /**
     * Getter
     * Prepare widget type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getData('instance_type');
    }

    /**
     * Setter
     * Prepare widget package theme
     *
     * @param string $packageTheme
     * @return Mage_Widget_Model_Widget_Instance
     */
    public function setPackageTheme($packageTheme)
    {
        $this->setData('package_theme', $packageTheme);
        return $this;
    }

    /**
     * Getter
     * Prepare widget package theme
     *
     * @return string
     */
    public function getPackageTheme()
    {
        return $this->_getData('package_theme');
    }

    /**
     * Getter.
     * If not set return default
     *
     * @return string
     */
    public function getArea()
    {
        if (!$this->_getData('area')) {
            return Mage_Core_Model_Design_Package::DEFAULT_AREA;
        }
        return $this->_getData('area');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getPackage()
    {
        if (!$this->_getData('package')) {
            $this->_parsePackageTheme();
        }
        return $this->_getData('package');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTheme()
    {
        if (!$this->_getData('theme')) {
            $this->_parsePackageTheme();
        }
        return $this->_getData('theme');
    }

    /**
     * Parse packageTheme and set parsed package and theme
     *
     * @return Mage_Widget_Model_Widget_Instance
     */
    protected function _parsePackageTheme()
    {
        if ($this->getPackageTheme() && strpos($this->getPackageTheme(), '/')) {
            list($package, $theme) = explode('/', $this->getPackageTheme());
            $this->setData('package', $package);
            $this->setData('theme', $theme);
        } else {
            $this->setData('package', Mage_Core_Model_Design_Package::DEFAULT_PACKAGE);
            $this->setData('theme', Mage_Core_Model_Design_Package::DEFAULT_THEME);
        }
        return $this;
    }

    /**
     * Getter
     * Explode to array if string setted
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (is_string($this->getData('store_ids'))) {
            return explode(',', $this->getData('store_ids'));
        }
        return $this->getData('store_ids');
    }

    /**
     * Getter
     * Unserialize if serialized string setted
     *
     * @return array
     */
    public function getWidgetParameters()
    {
        if (is_string($this->getData('widget_parameters'))) {
            return unserialize($this->getData('widget_parameters'));
        } else if (is_null($this->getData('widget_parameters'))) {
            return array();
        }
        return (is_array($this->getData('widget_parameters'))) ? $this->getData('widget_parameters') : array();
    }

    /**
     * Retrieve option array of widget types
     *
     * @return array
     */
    public function getWidgetsOptionArray()
    {
        $widgets = array();
        $widgetsArr = Mage::getSingleton('Mage_Widget_Model_Widget')->getWidgetsArray();
        foreach ($widgetsArr as $widget) {
            $widgets[] = array(
                'value' => $widget['type'],
                'label' => $widget['name']
            );
        }
        return $widgets;
    }

    /**
     * Load widget XML config and merge with theme widget config
     *
     * @return Varien_Simplexml_Element|null
     */
    public function getWidgetConfig()
    {
        if ($this->_widgetConfigXml === null) {
            $this->_widgetConfigXml = Mage::getSingleton('Mage_Widget_Model_Widget')
                ->getXmlElementByType($this->getType());
            if ($this->_widgetConfigXml) {
                $configFile = Mage::getDesign()->getFilename('widget.xml', array(
                    'area'    => $this->getArea(),
                    'package' => $this->getPackage(),
                    'theme'   => $this->getTheme(),
                    'module'  => Mage::getConfig()->determineOmittedNamespace(
                        preg_replace('/^(.+?)\/.+$/', '\\1', $this->getType()), true
                    ),
                ));

                if (is_readable($configFile)) {
                    $themeWidgetsConfig = new Varien_Simplexml_Config();
                    $themeWidgetsConfig->loadFile($configFile);
                    if ($themeWidgetTypeConfig = $themeWidgetsConfig->getNode($this->_widgetConfigXml->getName())) {
                        $this->_widgetConfigXml->extend($themeWidgetTypeConfig);
                    }
                }
            }
        }
        return $this->_widgetConfigXml;
    }

    /**
     * Retrieve widget availabel templates
     *
     * @return array
     */
    public function getWidgetTemplates()
    {
        $templates = array();
        if ($this->getWidgetConfig() && ($configTemplates = $this->getWidgetConfig()->parameters->template)) {
            if ($configTemplates->values && $configTemplates->values->children()) {
                foreach ($configTemplates->values->children() as $name => $template) {
                    $helper = $template->getAttribute('module') ? $template->getAttribute('module') : 'Mage_Widget_Helper_Data';
                    $templates[(string)$name] = array(
                        'value' => (string)$template->value,
                        'label' => Mage::helper($helper)->__((string)$template->label)
                    );
                }
            } elseif ($configTemplates->value) {
                $templates['default'] = array(
                    'value' => (string)$configTemplates->value,
                    'label' => Mage::helper('Mage_Widget_Helper_Data')->__('Default Template')
                );
            }
        }
        return $templates;
    }

    /**
     * Get list of containers that widget is limited to be in
     *
     * @return array
     */
    public function getWidgetSupportedContainers()
    {
        $containers = array();
        if ($this->getWidgetConfig() && ($configNodes = $this->getWidgetConfig()->supported_containers)) {
            foreach ($configNodes->children() as $node) {
                $containers[] = (string)$node->container_name;
            }
        }
        return $containers;
    }

    /**
     * Retrieve widget templates that supported by specified container name
     *
     * @param string $containerName
     * @return array
     */
    public function getWidgetSupportedTemplatesByContainer($containerName)
    {
        $templates = array();
        $widgetTemplates = $this->getWidgetTemplates();
        if ($this->getWidgetConfig()) {
            if (!($configNodes = $this->getWidgetConfig()->supported_containers)) {
                return $widgetTemplates;
            }
            foreach ($configNodes->children() as $node) {
                if ((string)$node->container_name == $containerName) {
                    if ($node->template && $node->template->children()) {
                        foreach ($node->template->children() as $template) {
                            if (isset($widgetTemplates[(string)$template])) {
                                $templates[] = $widgetTemplates[(string)$template];
                            }
                        }
                    } else {
                        $templates[] = $widgetTemplates[(string)$template];
                    }
                }
            }
        } else {
            return $widgetTemplates;
        }
        return $templates;
    }

    /**
     * Generate layout update xml
     *
     * @param string $container
     * @param string $templatePath
     * @return string
     */
    public function generateLayoutUpdateXml($container, $templatePath = '')
    {
        $templateFilename = Mage::getSingleton('Mage_Core_Model_Design_Package')->getFilename($templatePath, array(
            'area'    => $this->getArea(),
            'package' => $this->getPackage(),
            'theme'   => $this->getTheme(),
            'module'  => Mage_Core_Block_Abstract::extractModuleName($this->getType()),
        ));
        if (!$this->getId() && !$this->isCompleteToCreate()
            || ($templatePath && !is_readable($templateFilename)))
        {
            return '';
        }
        $parameters = $this->getWidgetParameters();
        $xml = '<reference name="' . $container . '">';
        $template = '';
        if (isset($parameters['template'])) {
            unset($parameters['template']);
        }
        if ($templatePath) {
            $template = ' template="' . $templatePath . '"';
        }

        $hash = Mage::helper('Mage_Core_Helper_Data')->uniqHash();
        $xml .= '<block type="' . $this->getType() . '" name="' . $hash . '"' . $template . '>';
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if ($name && strlen((string)$value)) {
                $xml .= '<action method="setData">'
                    . '<name>' . $name . '</name>'
                    . '<value>' . Mage::helper('Mage_Widget_Helper_Data')->escapeHtml($value) . '</value>'
                    . '</action>';
            }
        }
        $xml .= '</block></reference>';

        return $xml;
    }

    /**
     * Invalidate related cache types
     *
     * @return Mage_Widget_Model_Widget_Instance
     */
    protected function _invalidateCache()
    {
        $types = Mage::getConfig()->getNode(self::XML_NODE_RELATED_CACHE);
        if ($types) {
            $types = $types->asArray();
            Mage::app()->getCacheInstance()->invalidateType(array_keys($types));
        }
        return $this;
    }

    /**
     * Invalidate related cache if instance contain layout updates
     */
    protected function _afterSave()
    {
        if ($this->dataHasChangedFor('page_groups') || $this->dataHasChangedFor('widget_parameters')) {
            $this->_invalidateCache();
        }
        return parent::_afterSave();
    }

    /**
     * Invalidate related cache if instance contain layout updates
     */
    protected function _beforeDelete()
    {
        if ($this->getPageGroups()) {
            $this->_invalidateCache();
        }
        return parent::_beforeDelete();
    }
}
