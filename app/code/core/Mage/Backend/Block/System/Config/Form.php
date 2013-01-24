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
 * System config form block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_System_Config_Form extends Mage_Backend_Block_Widget_Form
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
     * Backend config data instance
     *
     * @var Mage_Backend_Model_Config
     */
    protected $_configDataObject;

    /**
     * System configuration root node
     *
     * @var Varien_Simplexml_Element
     */
    protected $_configRoot;

    /**
     * Default fieldset rendering block
     *
     * @var Mage_Backend_Block_System_Config_Form_Fieldset
     */
    protected $_fieldsetRenderer;

    /**
     * Default field rendering block
     *
     * @var Mage_Backend_Block_System_Config_Form_Field
     */
    protected $_fieldRenderer;

    /**
     * List of fieldset
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
     * Backend Config model factory
     *
     * @var Mage_Backend_Model_Config_Factory
     */
    protected $_configFactory;

    /**
     * Varien_Data_Form_Factory
     *
     * @var Varien_Data_Form_Factory
     */
    protected $_formFactory;

    /**
     * System config structure
     *
     * @var Mage_Backend_Model_Config_Structure
     */
    protected $_configStructure;

    /**
     *Form fieldset factory
     *
     * @var Mage_Backend_Block_System_Config_Form_Fieldset_Factory
     */
    protected $_fieldsetFactory;

    /**
     * Form field factory
     *
     * @var Mage_Backend_Block_System_Config_Form_Field_Factory
     */
    protected $_fieldFactory;

    /**
     * Form field factory
     *
     * @var Mage_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Magento_Filesystem $filesystem
     * @param Mage_Backend_Model_Config_Factory $configFactory
     * @param Varien_Data_Form_Factory $formFactory
     * @param Mage_Backend_Model_Config_Clone_Factory $cloneModelFactory
     * @param Mage_Backend_Model_Config_Structure $configStructure
     * @param Mage_Backend_Block_System_Config_Form_Fieldset_Factory $fieldsetFactory
     * @param Mage_Backend_Block_System_Config_Form_Field_Factory $fieldFactory
     * @param Mage_Core_Model_Config $coreConfig
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Magento_Filesystem $filesystem,
        Mage_Backend_Model_Config_Factory $configFactory,
        Varien_Data_Form_Factory $formFactory,
        Mage_Backend_Model_Config_Clone_Factory $cloneModelFactory,
        Mage_Backend_Model_Config_Structure $configStructure,
        Mage_Backend_Block_System_Config_Form_Fieldset_Factory $fieldsetFactory,
        Mage_Backend_Block_System_Config_Form_Field_Factory $fieldFactory,
        Mage_Core_Model_Config $coreConfig,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $filesystem, $data);
        $this->_configFactory = $configFactory;
        $this->_formFactory = $formFactory;
        $this->_cloneModelFactory = $cloneModelFactory;
        $this->_configStructure = $configStructure;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_fieldFactory = $fieldFactory;
        $this->_coreConfig = $coreConfig;

        $this->_scopeLabels = array(
            self::SCOPE_DEFAULT  => $this->helper('Mage_Backend_Helper_Data')->__('[GLOBAL]'),
            self::SCOPE_WEBSITES => $this->helper('Mage_Backend_Helper_Data')->__('[WEBSITE]'),
            self::SCOPE_STORES   => $this->helper('Mage_Backend_Helper_Data')->__('[STORE VIEW]'),
        );
    }

    /**
     * Initialize objects required to render config form
     *
     * @return Mage_Backend_Block_System_Config_Form
     */
    protected function _initObjects()
    {
        $this->_configRoot = $this->_coreConfig->getNode(null, $this->getScope(), $this->getScopeCode());

        $this->_configDataObject = $this->_configFactory->create(
            array(
                'section' => $this->getSectionCode(),
                'website' => $this->getWebsiteCode(),
                'store' => $this->getStoreCode()
            )
        );

        $this->_configData = $this->_configDataObject->load();
        $this->_fieldsetRenderer = $this->_fieldsetFactory->create();
        $this->_fieldRenderer = $this->_fieldFactory->create();
        return $this;
    }

    /**
     * Initialize form
     *
     * @return Mage_Backend_Block_System_Config_Form
     */
    public function initForm()
    {
        $this->_initObjects();

        /** @var Varien_Data_Form $form */
        $form = $this->_formFactory->create();
        /** @var $section Mage_Backend_Model_Config_Structure_Element_Section */
        $section = $this->_configStructure->getElement($this->getSectionCode());
        if ($section && $section->isVisible($this->getWebsiteCode(), $this->getStoreCode())) {
            foreach ($section->getChildren() as $group) {
                $this->_initGroup($group, $section, $form);
            }
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize config field group
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Group $group
     * @param Mage_Backend_Model_Config_Structure_Element_Section $section
     * @param Varien_Data_Form $form
     */
    protected function _initGroup(Mage_Backend_Model_Config_Structure_Element_Group $group,
        Mage_Backend_Model_Config_Structure_Element_Section $section,
        Varien_Data_Form_Abstract $form
    ) {
        $frontendModelClass = $group->getFrontendModel();
        $fieldsetRenderer = $frontendModelClass ?
            Mage::getBlockSingleton($frontendModelClass) :
            $this->_fieldsetRenderer;

        $fieldsetRenderer->setForm($this);
        $fieldsetRenderer->setConfigData($this->_configData);
        $fieldsetRenderer->setGroup($group);

        $fieldsetConfig = array(
            'legend' => $group->getLabel(),
            'comment' => $group->getComment(),
            'expanded' => $group->isExpanded()
        );

        $fieldset = $form->addFieldset($this->_generateElementId($group->getPath()), $fieldsetConfig);
        $fieldset->setRenderer($fieldsetRenderer);
        $group->populateFieldset($fieldset);
        $this->_addElementTypes($fieldset);

        $dependencies = $group->getDependencies($this->getStoreCode());
        $elementName = $this->_generateElementName($group->getPath());
        $elementId = $this->_generateElementId($group->getPath());

        $this->_populateDependenciesBlock($dependencies, $elementId, $elementName);

        if ($group->shouldCloneFields()) {
            $cloneModel = $group->getCloneModel();
            foreach ($cloneModel->getPrefixes() as $prefix) {
                $this->initFields($fieldset, $group, $section, $prefix['field'], $prefix['label']);
            }
        } else {
            $this->initFields($fieldset, $group, $section);
        }

        $this->_fieldsets[$group->getId()] = $fieldset;
    }

    /**
     * Return dependency block object
     *
     * @return Mage_Backend_Block_Widget_Form_Element_Dependence
     */
    protected function _getDependence()
    {
        if (!$this->getChildBlock('element_dependence')) {
            $this->addChild('element_dependence', 'Mage_Backend_Block_Widget_Form_Element_Dependence');
        }
        return $this->getChildBlock('element_dependence');
    }

    /**
     * Initialize config group fields
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Mage_Backend_Model_Config_Structure_Element_Group $group
     * @param Mage_Backend_Model_Config_Structure_Element_Section $section
     * @param string $fieldPrefix
     * @param string $labelPrefix
     * @return Mage_Backend_Block_System_Config_Form
     */
    public function initFields(
        Varien_Data_Form_Element_Fieldset $fieldset,
        Mage_Backend_Model_Config_Structure_Element_Group $group,
        Mage_Backend_Model_Config_Structure_Element_Section $section,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $extraConfigGroups = array();

        /** @var $element Mage_Backend_Model_Config_Structure_Element_Field */
        foreach ($group->getChildren() as $element) {
            if ($element instanceof Mage_Backend_Model_Config_Structure_Element_Group) {
                $this->_initGroup($element, $section, $fieldset);
            } else {
                $path = $element->getPath($fieldPrefix);
                if ($element->getSectionId() != $section->getId()) {
                    $groupPath = $element->getGroupPath();
                    if (!isset($extraConfigGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject
                            ->extendConfig($groupPath, false, $this->_configData);
                        $extraConfigGroups[$groupPath] = true;
                    }
                }
                $this->_initElement($element, $fieldset, $path, $fieldPrefix, $labelPrefix);
            }
        }
        return $this;
    }

    /**
     * Initialize form element
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Field $field
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param $path
     * @param string $fieldPrefix
     * @param string $labelPrefix
     */
    protected function _initElement(
        Mage_Backend_Model_Config_Structure_Element_Field $field,
        Varien_Data_Form_Element_Fieldset $fieldset,
        $path,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        $inherit = true;
        if (array_key_exists($path, $this->_configData)) {
            $data = $this->_configData[$path];
            $inherit = false;
        } elseif ($field->getConfigPath() !== null) {
            $data = $this->_configRoot->descend($field->getConfigPath());
        } else {
            $data = $this->_configRoot->descend($path);
        }
        $fieldRendererClass = $field->getFrontendModel();
        if ($fieldRendererClass) {
            $fieldRenderer = Mage::getBlockSingleton($fieldRendererClass);
        } else {
            $fieldRenderer = $this->_fieldRenderer;
        }

        $fieldRenderer->setForm($this);
        $fieldRenderer->setConfigData($this->_configData);

        $elementName = $this->_generateElementName($field->getPath(), $fieldPrefix);
        $elementId = $this->_generateElementId($field->getPath($fieldPrefix));

        if ($field->hasBackendModel()) {
            $backendModel = $field->getBackendModel();
            $backendModel->setPath($path)
                ->setValue($data)
                ->setWebsite($this->getWebsiteCode())
                ->setStore($this->getStoreCode())
                ->afterLoad();
            $data = $backendModel->getValue();
        }

        $dependencies = $field->getDependencies($fieldPrefix, $this->getStoreCode());
        $this->_populateDependenciesBlock($dependencies, $elementId, $elementName);


        $formField = $fieldset->addField($elementId, $field->getType(), array(
            'name' => $elementName,
            'label' => $field->getLabel($labelPrefix),
            'comment' => $field->getComment($data),
            'tooltip' => $field->getTooltip(),
            'hint' => $field->getHint(),
            'value' => $data,
            'inherit' => $inherit,
            'class' => $field->getFrontendClass(),
            'field_config' => $field->getData(),
            'scope' => $this->getScope(),
            'scope_id' => $this->getScopeId(),
            'scope_label' => $this->getScopeLabel($field),
            'can_use_default_value' => $this->canUseDefaultValue($field->showInDefault()),
            'can_use_website_value' => $this->canUseWebsiteValue($field->showInWebsite()),
        ));
        $field->populateInput($formField);

        if ($field->hasValidation()) {
            $formField->addClass($field->getValidation());
        }
        if ($field->getType() == 'multiselect') {
            $formField->setCanBeEmpty($field->canBeEmpty());
        }
        if ($field->hasSourceModel()) {
            $formField->setValues($field->getOptions());
        }
        $formField->setRenderer($fieldRenderer);
    }

    /**
     * Populate dependencies block
     *
     * @param array $dependencies
     * @param string $elementId
     * @param string $elementName
     */
    protected function _populateDependenciesBlock(array $dependencies, $elementId, $elementName)
    {
        foreach ($dependencies as $dependentId => $dependentValue) {
            $fieldNameFrom = $this->_generateElementName($dependentId, null, '_');
            $this->_getDependence()
                ->addFieldMap($elementId, $elementName)
                ->addFieldMap($this->_generateElementId($dependentId), $fieldNameFrom)
                ->addFieldDependence($elementName, $fieldNameFrom, $dependentValue);
        }
    }

    /**
     * Generate element name
     *
     * @param string $elementPath
     * @param string $fieldPrefix
     * @param string $separator
     * @return string
     */
    protected function _generateElementName($elementPath, $fieldPrefix = '', $separator = '/')
    {
        $part = explode($separator, $elementPath);
        array_shift($part); //shift section name
        $fieldId = array_pop($part);   //shift filed id
        $groupName = implode('][groups][', $part);
        $name = 'groups[' . $groupName . '][fields][' . $fieldPrefix . $fieldId . '][value]';
        return $name;
    }

    /**
     * Generate element id
     *
     * @param string $path
     * @return string
     */
    protected function _generateElementId($path)
    {
        return str_replace('/', '_', $path);
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
     *
     *
     * @return Mage_Backend_Block_Widget_Form|Mage_Core_Block_Abstract|void
     */
    protected function _beforeToHtml()
    {
        $this->initForm();
        return parent::_beforeToHtml();
    }

    /**
     * Append dependence block at then end of form block
     *
     * @param string $html
     * @return string
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
     * Check if can use default value
     *
     * @param int $fieldValue
     * @return boolean
     */
    public function canUseDefaultValue($fieldValue)
    {
        if ($this->getScope() == self::SCOPE_STORES && $fieldValue) {
            return true;
        }
        if ($this->getScope() == self::SCOPE_WEBSITES && $fieldValue) {
            return true;
        }
        return false;
    }

    /**
     * Check if can use website value
     *
     * @param int $fieldValue
     * @return boolean
     */
    public function canUseWebsiteValue($fieldValue)
    {
        if ($this->getScope() == self::SCOPE_STORES && $fieldValue) {
            return true;
        }
        return false;
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
     * @param Mage_Backend_Model_Config_Structure_Element_Field $field
     * @return string
     */
    public function getScopeLabel(Mage_Backend_Model_Config_Structure_Element_Field $field)
    {
        $showInStore = $field->showInStore();
        $showInWebsite = $field->showInWebsite();

        if ($showInStore == 1) {
            return $this->_scopeLabels[self::SCOPE_STORES];
        } elseif ($showInWebsite == 1) {
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
     * Get additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'export' => $this->_coreConfig
                ->getBlockClassName('Mage_Backend_Block_System_Config_Form_Field_Export'),
            'import' => $this->_coreConfig
                 ->getBlockClassName('Mage_Backend_Block_System_Config_Form_Field_Import'),
            'allowspecific' => $this->_coreConfig
                ->getBlockClassName('Mage_Backend_Block_System_Config_Form_Field_Select_Allowspecific'),
            'image' => $this->_coreConfig
                ->getBlockClassName('Mage_Backend_Block_System_Config_Form_Field_Image'),
            'file' => $this->_coreConfig
                ->getBlockClassName('Mage_Backend_Block_System_Config_Form_Field_File')
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
