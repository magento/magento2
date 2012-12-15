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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_Element_Field
    extends Mage_Backend_Model_Config_Structure_ElementAbstract
{
    /**
     * Backend model factory
     *
     * @var Mage_Backend_Model_Config_BackendFactory
     */
    protected $_backendFactory;

    /**
     * Source model factory
     *
     * @var Mage_Backend_Model_Config_SourceFactory
     */
    protected $_sourceFactory;

    /**
     * Comment model factory
     *
     * @var Mage_Backend_Model_Config_CommentFactory
     */
    protected $_commentFactory;

    /**
     *
     * @var Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper
     */
    protected $_dependencyMapper;

    /**
     * Block factory
     *
     * @var Mage_Core_Model_BlockFactory
     */
    protected $_blockFactory;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $application
     * @param Mage_Backend_Model_Config_BackendFactory $backendFactory
     * @param Mage_Backend_Model_Config_SourceFactory $sourceFactory
     * @param Mage_Backend_Model_Config_CommentFactory $commentFactory
     * @param Mage_Core_Model_BlockFactory $blockFactory
     * @param Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper $dependencyMapper
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $application,
        Mage_Backend_Model_Config_BackendFactory $backendFactory,
        Mage_Backend_Model_Config_SourceFactory $sourceFactory,
        Mage_Backend_Model_Config_CommentFactory $commentFactory,
        Mage_Core_Model_BlockFactory $blockFactory,
        Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper $dependencyMapper
    ) {
        parent::__construct($helperFactory, $application);
        $this->_backendFactory = $backendFactory;
        $this->_sourceFactory = $sourceFactory;
        $this->_commentFactory = $commentFactory;
        $this->_blockFactory = $blockFactory;
        $this->_dependencyMapper = $dependencyMapper;
    }

    /**
     * Retrieve field label
     *
     * @param string $labelPrefix
     * @return string
     */
    public function getLabel($labelPrefix = '')
    {
        $label = '';
        if ($labelPrefix) {
            $label .= $this->_helperFactory->get($this->_getTranslationModule())->__($labelPrefix) . ' ';
        }
        $label .= parent::getLabel();
        return $label;
    }

    /**
     * Retrieve field hint
     *
     * @return string
     */
    public function getHint()
    {
        return $this->_getTranslatedAttribute('hint');
    }

    /**
     * Retrieve comment
     *
     * @param string $currentValue
     * @return string
     */
    public function getComment($currentValue = '')
    {
        $comment = '';
        if (isset($this->_data['comment'])) {
            if (is_array($this->_data['comment'])) {
                if (isset($this->_data['comment']['model'])) {
                    $model = $this->_commentFactory->create($this->_data['comment']['model']);
                    $comment = $model->getCommentText($currentValue);
                }
            } else {
                $comment = parent::getComment();
            }
        }
        return $comment;
    }

    /**
     * Retrieve tooltip text
     *
     * @return string
     */
    public function getTooltip()
    {
        if (isset($this->_data['tooltip'])) {
            return $this->_getTranslatedAttribute('tooltip');
        } elseif (isset($this->_data['tooltip_block'])) {
            return $this->_blockFactory->createBlock($this->_data['tooltip_block'])->toHtml();
        }
        return '';
    }

    /**
     * Retrieve field type
     *
     * @return string
     */
    public function getType()
    {
        return isset($this->_data['type']) ? $this->_data['type'] : 'text';
    }

    /**
     * Retrieve frontend css class
     *
     * @return string
     */
    public function getFrontendClass()
    {
        return isset($this->_data['frontend_class']) ? $this->_data['frontend_class'] : '';
    }

    /**
     * Check whether field has backend model
     *
     * @return bool
     */
    public function hasBackendModel()
    {
        return array_key_exists('backend_model', $this->_data) && $this->_data['backend_model'];
    }

    /**
     * Retrieve backend model
     *
     * @return Mage_Core_Model_Config_Data
     */
    public function getBackendModel()
    {
        return $this->_backendFactory->create($this->_data['backend_model']);
    }

    /**
     * Retrieve field section id
     *
     * @return string
     */
    public function getSectionId()
    {
        $parts = explode('/', $this->getPath());
        return current($parts);
    }

    /**
     * Retrieve field group path
     *
     * @return string
     */
    public function getGroupPath()
    {
        return dirname($this->getPath());
    }

    /**
     * Retrieve config path
     *
     * @return null|string
     */
    public function getConfigPath()
    {
        return isset($this->_data['config_path']) ? $this->_data['config_path'] : null;
    }

    /**
     * Check whether field should be shown in default scope
     *
     * @return bool
     */
    public function showInDefault()
    {
        return isset($this->_data['showInDefault']) && (int)$this->_data['showInDefault'];
    }

    /**
     * Check whether field should be shown in website scope
     *
     * @return bool
     */
    public function showInWebsite()
    {
        return isset($this->_data['showInWebsite']) && (int)$this->_data['showInWebsite'];
    }

    /**
     * Check whether field should be shown in store scope
     *
     * @return bool
     */
    public function showInStore()
    {
        return isset($this->_data['showInStore']) && (int)$this->_data['showInStore'];
    }

    /**
     * Populate form element with field data
     *
     * @param Varien_Data_Form_Element_Abstract $formField
     */
    public function populateInput($formField)
    {
        $originalData = array();
        foreach ($this->_data as $key => $value) {
            if (!is_array($value)) {
                $originalData[$key] = $value;
            }
        }
        $formField->setOriginalData($originalData);
    }

    /**
     * Check whether field has validation class
     *
     * @return bool
     */
    public function hasValidation()
    {
        return isset($this->_data['validate']);
    }

    /**
     * Retrieve field validation class
     *
     * @return string
     */
    public function getValidation()
    {
        return $this->_data['validate'];
    }

    /**
     * Check whether field can be empty
     *
     * @return bool
     */
    public function canBeEmpty()
    {
        return isset($this->_data['can_be_empty']);
    }

    /**
     * Check whether field has source model
     *
     * @return bool
     */
    public function hasSourceModel()
    {
        return isset($this->_data['source_model']);
    }

    /**
     * Retrieve source model option list
     *
     * @return array
     */
    public function getOptions()
    {
        $factoryName = $this->_data['source_model'];
        $method = false;
        if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
            array_shift($matches);
            list($factoryName, $method) = array_values($matches);
        }

        $sourceModel = $this->_sourceFactory->create($factoryName);
        if ($sourceModel instanceof Varien_Object) {
            $sourceModel->setPath($this->getPath());
        }
        if ($method) {
            if ($this->getType() == 'multiselect') {
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
            $optionArray = $sourceModel->toOptionArray($this->getType() == 'multiselect');
        }
        return $optionArray;
    }

    /**
     * Retrieve field dependencies
     *
     * @param $fieldPrefix
     * @param $storeCode
     * @return array
     */
    public function getDependencies($fieldPrefix, $storeCode)
    {
        $dependencies = array();
        if (false == isset($this->_data['depends']['fields'])) {
            return $dependencies;
        }
        $dependencies = $this->_dependencyMapper->getDependencies(
            $this->_data['depends']['fields'],
            $storeCode,
            $fieldPrefix
        );
        return $dependencies;
    }

    /**
     * Check whether element should be displayed for advanced users
     *
     * @return bool
     */
    public function isAdvanced()
    {
        return isset($this->_data['advanced']) && $this->_data['advanced'];
    }
}
