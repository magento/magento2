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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * System config form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Config_Form extends Mage_Adminhtml_Block_Widget_Form
{

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITES = 'websites';
    const SCOPE_STORES   = 'stores';

    /**
     * Config data array
     *
     * @var array
     */
    protected $_configData;

    /**
     * Adminhtml config data instance
     *
     * @var Mage_Adminhtml_Model_Config_Data
     */
    protected $_configDataObject;

    /**
     * Enter description here...
     *
     * @var Varien_Simplexml_Element
     */
    protected $_configRoot;

    /**
     * Enter description here...
     *
     * @var Mage_Adminhtml_Model_Config
     */
    protected $_configFields;

    /**
     * Enter description here...
     *
     * @var Mage_Adminhtml_Block_System_Config_Form_Fieldset
     */
    protected $_defaultFieldsetRenderer;

    /**
     * Enter description here...
     *
     * @var Mage_Adminhtml_Block_System_Config_Form_Field
     */
    protected $_defaultFieldRenderer;

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_fieldsets = array();

    /**
     * Translated scope labels
     *
     * @var array
     */
    protected $_scopeLabels = array();

    /**
     * Enter description here...
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_scopeLabels = array(
            self::SCOPE_DEFAULT  => Mage::helper('Mage_Adminhtml_Helper_Data')->__('[GLOBAL]'),
            self::SCOPE_WEBSITES => Mage::helper('Mage_Adminhtml_Helper_Data')->__('[WEBSITE]'),
            self::SCOPE_STORES   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('[STORE VIEW]'),
        );
    }

    /**
     * Enter description here...
     *
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    protected function _initObjects()
    {
        $this->_configRoot = Mage::getConfig()->getNode(null, $this->getScope(), $this->getScopeCode());

        $this->_configDataObject = Mage::getModel('Mage_Adminhtml_Model_Config_Data')
            ->setSection($this->getSectionCode())
            ->setWebsite($this->getWebsiteCode())
            ->setStore($this->getStoreCode());

        $this->_configData = $this->_configDataObject->load();

        $this->_configFields = Mage::getSingleton('Mage_Adminhtml_Model_Config');

        $this->_defaultFieldsetRenderer = Mage::getBlockSingleton('Mage_Adminhtml_Block_System_Config_Form_Fieldset');
        $this->_defaultFieldRenderer = Mage::getBlockSingleton('Mage_Adminhtml_Block_System_Config_Form_Field');
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    public function initForm()
    {
        $this->_initObjects();

        $form = new Varien_Data_Form();

        $sections = $this->_configFields->getSection(
            $this->getSectionCode(),
            $this->getWebsiteCode(),
            $this->getStoreCode()
        );
        if (empty($sections)) {
            $sections = array();
        }
        foreach ($sections as $section) {
            /* @var $section Varien_Simplexml_Element */
            if (!$this->_canShowField($section)) {
                continue;
            }
            foreach ($section->groups as $groups){

                $groups = (array)$groups;
                usort($groups, array($this, '_sortForm'));

                foreach ($groups as $group){
                    /* @var $group Varien_Simplexml_Element */
                    if (!$this->_canShowField($group)) {
                        continue;
                    }

                    if ($group->frontend_model) {
                        $fieldsetRenderer = Mage::getBlockSingleton((string)$group->frontend_model);
                    } else {
                        $fieldsetRenderer = $this->_defaultFieldsetRenderer;
                    }

                    $fieldsetRenderer->setForm($this);
                    $fieldsetRenderer->setConfigData($this->_configData);
                    $fieldsetRenderer->setGroup($group);

                    if ($this->_configFields->hasChildren($group, $this->getWebsiteCode(), $this->getStoreCode())) {

                        $helperName = $this->_configFields->getAttributeModule($section, $group);

                        $fieldsetConfig = array('legend' => Mage::helper($helperName)->__((string)$group->label));
                        if (!empty($group->comment)) {
                            $fieldsetConfig['comment'] = Mage::helper($helperName)->__((string)$group->comment);
                        }
                        if (!empty($group->expanded)) {
                            $fieldsetConfig['expanded'] = (bool)$group->expanded;
                        }

                        $fieldset = $form->addFieldset(
                            $section->getName() . '_' . $group->getName(), $fieldsetConfig)
                            ->setRenderer($fieldsetRenderer);
                        $this->_prepareFieldOriginalData($fieldset, $group);
                        $this->_addElementTypes($fieldset);

                        if ($group->clone_fields) {
                            if ($group->clone_model) {
                                $cloneModel = Mage::getModel((string)$group->clone_model);
                            } else {
                                Mage::throwException(
                                    'Config form fieldset clone model required to be able to clone fields'
                                );
                            }
                            foreach ($cloneModel->getPrefixes() as $prefix) {
                                $this->initFields($fieldset, $group, $section, $prefix['field'], $prefix['label']);
                            }
                        } else {
                            $this->initFields($fieldset, $group, $section);
                        }

                        $this->_fieldsets[$group->getName()] = $fieldset;

                    }
                }
            }
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * Return dependency block object
     *
     * @return Mage_Adminhtml_Block_Widget_Form_Element_Dependence
     */
    protected function _getDependence()
    {
        if (!$this->getChild('element_dependense')){
            $this->setChild('element_dependense',
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence'));
        }
        return $this->getChild('element_dependense');
    }

    /**
     * Init fieldset fields
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Varien_Simplexml_Element $group
     * @param Varien_Simplexml_Element $section
     * @param string $fieldPrefix
     * @param string $labelPrefix
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    public function initFields($fieldset, $group, $section, $fieldPrefix='', $labelPrefix='')
    {
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $configDataAdditionalGroups = array();

        foreach ($group->fields as $elements) {

            $elements = (array)$elements;
            // sort either by sort_order or by child node values bypassing the sort_order
            if ($group->sort_fields && $group->sort_fields->by) {
                $fieldset->setSortElementsByAttribute((string)$group->sort_fields->by,
                    ($group->sort_fields->direction_desc ? SORT_DESC : SORT_ASC)
                );
            } else {
                usort($elements, array($this, '_sortForm'));
            }

            foreach ($elements as $e) {
                if (!$this->_canShowField($e)) {
                    continue;
                }

                /**
                 * Look for custom defined field path
                 */
                $path = (string)$e->config_path;
                if (empty($path)) {
                    $path = $section->getName() . '/' . $group->getName() . '/' . $fieldPrefix . $e->getName();
                } elseif (strrpos($path, '/') > 0) {
                    // Extend config data with new section group
                    $groupPath = substr($path, 0, strrpos($path, '/'));
                    if (!isset($configDataAdditionalGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject->extendConfig(
                            $groupPath,
                            false,
                            $this->_configData
                        );
                        $configDataAdditionalGroups[$groupPath] = true;
                    }
                }

                $id = $section->getName() . '_' . $group->getName() . '_' . $fieldPrefix . $e->getName();

                if (isset($this->_configData[$path])) {
                    $data = $this->_configData[$path];
                    $inherit = false;
                } else {
                    $data = $this->_configRoot->descend($path);
                    $inherit = true;
                }
                if ($e->frontend_model) {
                    $fieldRenderer = Mage::getBlockSingleton((string)$e->frontend_model);
                } else {
                    $fieldRenderer = $this->_defaultFieldRenderer;
                }

                $fieldRenderer->setForm($this);
                $fieldRenderer->setConfigData($this->_configData);

                $helperName = $this->_configFields->getAttributeModule($section, $group, $e);
                $fieldType  = (string)$e->frontend_type ? (string)$e->frontend_type : 'text';
                $name       = 'groups['.$group->getName().'][fields]['.$fieldPrefix.$e->getName().'][value]';
                $label      =  Mage::helper($helperName)->__($labelPrefix).' '.Mage::helper($helperName)->__((string)$e->label);
                $hint       = (string)$e->hint ? Mage::helper($helperName)->__((string)$e->hint) : '';

                if ($e->backend_model) {
                    $model = Mage::getModel((string)$e->backend_model);
                    if (!$model instanceof Mage_Core_Model_Config_Data) {
                        Mage::throwException('Invalid config field backend model: '.(string)$e->backend_model);
                    }
                    $model->setPath($path)
                        ->setValue($data)
                        ->setWebsite($this->getWebsiteCode())
                        ->setStore($this->getStoreCode())
                        ->afterLoad();
                    $data = $model->getValue();
                }

                $comment    = $this->_prepareFieldComment($e, $helperName, $data);
                $tooltip    = $this->_prepareFieldTooltip($e, $helperName);

                if ($e->depends) {
                    foreach ($e->depends->children() as $dependent) {
                        $dependentId = $section->getName()
                            . '_' . $group->getName()
                            . '_' . $fieldPrefix
                            . $dependent->getName();
                        $shouldBeAddedDependence = true;
                        $dependentValue          = (string) $dependent;
                        $dependentFieldName      = $fieldPrefix . $dependent->getName();
                        $dependentField          = $group->fields->$dependentFieldName;
                        /*
                         * If dependent field can't be shown in current scope and real dependent config value
                         * is not equal to preferred one, then hide dependence fields by adding dependence
                         * based on not shown field (not rendered field)
                         */
                        if (!$this->_canShowField($dependentField)) {
                            $dependentFullPath = $section->getName()
                                . '/' . $group->getName()
                                . '/' . $fieldPrefix
                                . $dependent->getName();
                            $shouldBeAddedDependence = $dependentValue != Mage::getStoreConfig(
                                $dependentFullPath,
                                $this->getStoreCode()
                            );
                        }
                        if($shouldBeAddedDependence) {
                            $this->_getDependence()
                                ->addFieldMap($id, $id)
                                ->addFieldMap($dependentId, $dependentId)
                                ->addFieldDependence($id, $dependentId, $dependentValue);
                        }
                    }
                }

                $field = $fieldset->addField($id, $fieldType, array(
                    'name'                  => $name,
                    'label'                 => $label,
                    'comment'               => $comment,
                    'tooltip'               => $tooltip,
                    'hint'                  => $hint,
                    'value'                 => $data,
                    'inherit'               => $inherit,
                    'class'                 => $e->frontend_class,
                    'field_config'          => $e,
                    'scope'                 => $this->getScope(),
                    'scope_id'              => $this->getScopeId(),
                    'scope_label'           => $this->getScopeLabel($e),
                    'can_use_default_value' => $this->canUseDefaultValue((int)$e->show_in_default),
                    'can_use_website_value' => $this->canUseWebsiteValue((int)$e->show_in_website),
                ));
                $this->_prepareFieldOriginalData($field, $e);

                if (isset($e->validate)) {
                    $field->addClass($e->validate);
                }

                if (isset($e->frontend_type)
                    && 'multiselect' === (string)$e->frontend_type
                    && isset($e->can_be_empty)
                ) {
                    $field->setCanBeEmpty(true);
                }

                $field->setRenderer($fieldRenderer);

                if ($e->source_model) {
                    // determine callback for the source model
                    $factoryName = (string)$e->source_model;
                    $method = false;
                    if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
                        array_shift($matches);
                        list($factoryName, $method) = array_values($matches);
                    }

                    $sourceModel = Mage::getSingleton($factoryName);
                    if ($sourceModel instanceof Varien_Object) {
                        $sourceModel->setPath($path);
                    }
                    if ($method) {
                        if ($fieldType == 'multiselect') {
                            $optionArray = $sourceModel->$method();
                        } else {
                            $optionArray = array();
                            foreach ($sourceModel->$method() as $key => $value) {
                                if (is_array($value)) {
                                    $optionArray[] = $value;
                                } else {
                                    $optionArray[] = array('label' => $value, 'value' => $key);
                                }
                            }
                        }
                    } else {
                        $optionArray = $sourceModel->toOptionArray($fieldType == 'multiselect');
                    }
                    $field->setValues($optionArray);
                }
            }
        }
        return $this;
    }

    /**
     * Return config root node for current scope
     *
     * @return Varien_Simplexml_Element
     */
    public function getConfigRoot()
    {
        if (empty($this->_configRoot)) {
            $this->_configRoot = Mage::getConfig()->getNode(null, $this->getScope(), $this->getScopeCode());
        }
        return $this->_configRoot;
    }

    /**
     * Set "original_data" array to the element, composed from nodes with scalar values
     *
     * @param Varien_Data_Form_Element_Abstract $field
     * @param Varien_Simplexml_Element $xmlElement
     */
    protected function _prepareFieldOriginalData($field, $xmlElement)
    {
        $originalData = array();
        foreach ($xmlElement as $key => $value) {
            if (!$value->hasChildren()) {
                $originalData[$key] = (string)$value;
            }
        }
        $field->setOriginalData($originalData);
    }

    /**
     * Support models "getCommentText" method for field note generation
     *
     * @param Mage_Core_Model_Config_Element $element
     * @param string $helper
     * @return string
     */
    protected function _prepareFieldComment($element, $helper, $currentValue)
    {
        $comment = '';
        if ($element->comment) {
            $commentInfo = $element->comment->asArray();
            if (is_array($commentInfo)) {
                if (isset($commentInfo['model'])) {
                    $model = Mage::getModel($commentInfo['model']);
                    if (method_exists($model, 'getCommentText')) {
                        $comment = $model->getCommentText($element, $currentValue);
                    }
                }
            } else {
                $comment = Mage::helper($helper)->__($commentInfo);
            }
        }
        return $comment;
    }

    /**
     * Prepare additional comment for field like tooltip
     *
     * @param Mage_Core_Model_Config_Element $element
     * @param string $helper
     * @return string
     */
    protected function _prepareFieldTooltip($element, $helper)
    {
        if ($element->tooltip) {
            return Mage::helper($helper)->__((string)$element->tooltip);
        } elseif ($element->tooltip_block) {
            return $this->getLayout()->createBlock((string)$element->tooltip_block)->toHtml();
        }
        return '';
    }

    /**
     * Append dependence block at then end of form block
     *
     *
     */
    protected function _afterToHtml($html)
    {
        if ($this->_getDependence()) {
            $html .= $this->_getDependence()->toHtml();
        }
        $html = parent::_afterToHtml($html);
        return $html;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $a
     * @param Varien_Simplexml_Element $b
     * @return boolean
     */
    protected function _sortForm($a, $b)
    {
        return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);

    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $field
     * @return boolean
     */
    public function canUseDefaultValue($field)
    {
        if ($this->getScope() == self::SCOPE_STORES && $field) {
            return true;
        }
        if ($this->getScope() == self::SCOPE_WEBSITES && $field) {
            return true;
        }
        return false;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $field
     * @return boolean
     */
    public function canUseWebsiteValue($field)
    {
        if ($this->getScope() == self::SCOPE_STORES && $field) {
            return true;
        }
        return false;
    }

    /**
     * Checking field visibility
     *
     * @param   Varien_Simplexml_Element $field
     * @return  bool
     */
    protected function _canShowField($field)
    {
        $ifModuleEnabled = trim((string)$field->if_module_enabled);
        if ($ifModuleEnabled && !Mage::helper('Mage_Core_Helper_Data')->isModuleEnabled($ifModuleEnabled)) {
            return false;
        }

        switch ($this->getScope()) {
            case self::SCOPE_DEFAULT:
                return (int)$field->show_in_default;
                break;
            case self::SCOPE_WEBSITES:
                return (int)$field->show_in_website;
                break;
            case self::SCOPE_STORES:
                return (int)$field->show_in_store;
                break;
        }
        return true;
    }

    /**
     * Retrieve current scope
     *
     * @return string
     */
    public function getScope()
    {
        $scope = $this->getData('scope');
        if (is_null($scope)) {
            if ($this->getStoreCode()) {
                $scope = self::SCOPE_STORES;
            } elseif ($this->getWebsiteCode()) {
                $scope = self::SCOPE_WEBSITES;
            } else {
                $scope = self::SCOPE_DEFAULT;
            }
            $this->setScope($scope);
        }

        return $scope;
    }

    /**
     * Retrieve label for scope
     *
     * @param Mage_Core_Model_Config_Element $element
     * @return string
     */
    public function getScopeLabel($element)
    {
        if ($element->show_in_store == 1) {
            return $this->_scopeLabels[self::SCOPE_STORES];
        } elseif ($element->show_in_website == 1) {
            return $this->_scopeLabels[self::SCOPE_WEBSITES];
        }
        return $this->_scopeLabels[self::SCOPE_DEFAULT];
    }

    /**
     * Get current scope code
     *
     * @return string
     */
    public function getScopeCode()
    {
        $scopeCode = $this->getData('scope_code');
        if (is_null($scopeCode)) {
            if ($this->getStoreCode()) {
                $scopeCode = $this->getStoreCode();
            } elseif ($this->getWebsiteCode()) {
                $scopeCode = $this->getWebsiteCode();
            } else {
                $scopeCode = '';
            }
            $this->setScopeCode($scopeCode);
        }

        return $scopeCode;
    }

    /**
     * Get current scope code
     *
     * @return int|string
     */
    public function getScopeId()
    {
        $scopeId = $this->getData('scope_id');
        if (is_null($scopeId)) {
            if ($this->getStoreCode()) {
                $scopeId = Mage::app()->getStore($this->getStoreCode())->getId();
            } elseif ($this->getWebsiteCode()) {
                $scopeId = Mage::app()->getWebsite($this->getWebsiteCode())->getId();
            } else {
                $scopeId = '';
            }
            $this->setScopeId($scopeId);
        }
        return $scopeId;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'export' => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_System_Config_Form_Field_Export'),
            'import' => Mage::getConfig()
                 ->getBlockClassName('Mage_Adminhtml_Block_System_Config_Form_Field_Import'),
            'allowspecific' => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_System_Config_Form_Field_Select_Allowspecific'),
            'image' => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_System_Config_Form_Field_Image'),
            'file' => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_System_Config_Form_Field_File')
        );
    }

    /**
     * Temporary moved those $this->getRequest()->getParam('blabla') from the code accross this block
     * to getBlala() methods to be later set from controller with setters
     */
    /**
     * Enter description here...
     *
     * @TODO delete this methods when {^see above^} is done
     * @return string
     */
    public function getSectionCode()
    {
        return $this->getRequest()->getParam('section', '');
    }

    /**
     * Enter description here...
     *
     * @TODO delete this methods when {^see above^} is done
     * @return string
     */
    public function getWebsiteCode()
    {
        return $this->getRequest()->getParam('website', '');
    }

    /**
     * Enter description here...
     *
     * @TODO delete this methods when {^see above^} is done
     * @return string
     */
    public function getStoreCode()
    {
        return $this->getRequest()->getParam('store', '');
    }

}
